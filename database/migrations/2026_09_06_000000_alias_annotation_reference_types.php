<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use ThreeLeaf\Biblioteca\Models\Annotation;

/**
 * Rewrite {@link Annotation} reference types from class names to morph aliases.
 *
 * Releases before 3.0.0 stored the fully-qualified class name of the referenced
 * {@link \ThreeLeaf\Biblioteca\Models\Paragraph} or
 * {@link \ThreeLeaf\Biblioteca\Models\Sentence}. 3.0.0 registers a morph map, so new rows
 * store the alias instead. Existing rows are rewritten here, because a mixed column would
 * leave `$paragraph->annotations()` — which constrains on the alias — silently missing
 * every row written before the upgrade.
 *
 * Rows holding anything else are **left alone**. A value the package never wrote is either
 * a host application's own discriminator or a planted one, and this migration cannot tell
 * them apart: the host's morph map is not necessarily registered in the process that runs
 * migrations. Issue #13 established that clearing such rows destroys legitimate data, and
 * the model refuses to resolve an impermissible value in any case. Auditing them is the
 * operator's decision, not this migration's.
 */
return new class extends Migration {

    /** Rewrite stored class names to their morph aliases. */
    public function up(): void
    {
        foreach (Annotation::REFERENCE_TYPES as $alias => $class) {
            $this->rewrite($class, $alias);
        }
    }

    /** Rewrite stored morph aliases back to their class names. */
    public function down(): void
    {
        foreach (Annotation::REFERENCE_TYPES as $alias => $class) {
            $this->rewrite($alias, $class);
        }
    }

    /**
     * Replace one stored reference type with another, matching case-insensitively.
     *
     * The match ignores letter case because PHP resolves class names that way, so a row
     * holding a differently-cased class name denotes the same model and
     * {@link Annotation::resolveReferenceType()} has always accepted it. Comparing with
     * <code>LOWER()</code> rather than a collation keeps the behaviour identical on SQLite,
     * MySQL, and PostgreSQL.
     *
     * @param string $from The stored value to replace.
     * @param string $to   The value to store instead.
     *
     * @return void
     */
    private function rewrite(string $from, string $to): void
    {
        DB::table(Annotation::TABLE_NAME)
            ->whereRaw('LOWER(reference_type) = ?', [strtolower($from)])
            ->update(['reference_type' => $to]);
    }
};
