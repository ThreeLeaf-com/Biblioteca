<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Providers\BibliotecaServiceProvider;

/**
 * Rewrite stored annotation reference types to their morph aliases.
 *
 * Releases before {@link BibliotecaServiceProvider::MORPH_MAP} stored
 * <code>reference_type</code> as a fully-qualified class name. New rows store the alias,
 * so rows written by an earlier release are rewritten here to keep the column consistent
 * and queryable.
 */
return new class extends Migration {

    /**
     * Rewrite each legacy class name to its morph alias.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable(Annotation::TABLE_NAME)) {
            return;
        }

        foreach (BibliotecaServiceProvider::MORPH_MAP as $alias => $modelClass) {
            DB::table(Annotation::TABLE_NAME)
                ->whereIn('reference_type', [$modelClass, '\\' . $modelClass])
                ->update(['reference_type' => $alias]);
        }
    }

    /**
     * Restore the fully-qualified class names.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable(Annotation::TABLE_NAME)) {
            return;
        }

        foreach (BibliotecaServiceProvider::MORPH_MAP as $alias => $modelClass) {
            DB::table(Annotation::TABLE_NAME)
                ->where('reference_type', $alias)
                ->update(['reference_type' => $modelClass]);
        }
    }
};
