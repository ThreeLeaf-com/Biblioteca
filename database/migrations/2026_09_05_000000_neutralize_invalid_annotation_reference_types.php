<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ThreeLeaf\Biblioteca\Exceptions\InvalidReferenceTypeException;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * Clear annotation reference types that do not denote a permitted model.
 *
 * <code>reference_type</code> is turned into <code>new $class</code> at several points in
 * Eloquent, and not all of them run through {@link Annotation}. Relationship-existence
 * queries — <code>has()</code>, <code>doesntHave()</code>, <code>whereHasMorph()</code> —
 * read the column straight from the table and instantiate what they find. Guarding the
 * model's own read paths therefore does not make every path safe; keeping impermissible
 * values out of the column does, for every path at once and for any Eloquent adds later.
 *
 * A row written by a release that did not constrain the column keeps its
 * <code>content</code>; only the reference is cleared, because it named something this
 * package cannot legitimately point at.
 */
return new class extends Migration {

    /**
     * Make the column nullable, then clear every value that is not a permitted model.
     *
     * The check runs through {@link Annotation::resolveReferenceType()} rather than a
     * literal list, so a value the host's own morph map resolves to {@link Annotation}'s
     * permitted models is kept.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable(Annotation::TABLE_NAME)) {
            return;
        }

        Schema::table(Annotation::TABLE_NAME, function ($table) {
            $table->string('reference_type')
                ->nullable()
                ->comment('The referenced model, or null when a stored value named something impermissible')
                ->change();
        });

        $storedTypes = DB::table(Annotation::TABLE_NAME)
            ->select('reference_type')
            ->distinct()
            ->pluck('reference_type')
            ->filter()
            ->all();

        $impermissible = [];

        foreach ($storedTypes as $storedType) {
            try {
                Annotation::resolveReferenceType($storedType);
            } catch (InvalidReferenceTypeException) {
                $impermissible[] = $storedType;
            }
        }

        if ($impermissible === []) {
            return;
        }

        $cleared = DB::table(Annotation::TABLE_NAME)
            ->whereIn('reference_type', $impermissible)
            ->update(['reference_type' => null]);

        /* Loud on purpose: a non-zero count here means rows were written that this package could not have written. */
        logger()->warning(
            'Biblioteca cleared impermissible annotation reference types.',
            ['rows' => $cleared, 'values' => $impermissible],
        );
    }

    /**
     * Restore the column to NOT NULL.
     *
     * The cleared values are **not** restored. They named classes outside
     * {@link Annotation::REFERENCE_TYPES}, so putting them back would reintroduce exactly
     * what <code>up()</code> removed. Rows cleared by <code>up()</code> block this
     * rollback rather than being silently deleted or given a fabricated reference; clear
     * them yourself first if you need to roll back.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable(Annotation::TABLE_NAME)) {
            return;
        }

        Schema::table(Annotation::TABLE_NAME, function ($table) {
            $table->string('reference_type')
                ->nullable(false)
                ->comment('The reference type / class')
                ->change();
        });
    }
};
