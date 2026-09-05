<?php

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rewrite annotation reference types from class names to morph aliases.
 *
 * Releases before 3.0.0 stored the fully-qualified class name of the referenced paragraph
 * or sentence. 3.0.0 registers a morph map, so new rows store the alias instead. Existing
 * rows are rewritten here, because a mixed column would leave `$paragraph->annotations()` —
 * which constrains on the alias — silently missing every row written before the upgrade.
 *
 * Rows holding anything else are **left alone**. A value the package never wrote is either
 * a host application's own discriminator or a planted one, and this migration cannot tell
 * them apart. Clearing such rows destroys legitimate data belonging to a host that keeps a
 * morph map of its own, and the model refuses to resolve an impermissible value in any
 * case. Auditing them is the operator's decision, not this migration's.
 *
 * **The values below are frozen at the 3.0.0 shape on purpose.** A migration is replayed by
 * every fresh install and must keep doing what it did the day it was written, so it does not
 * read the model's allow-list: a later release that adds a model, renames an alias, or
 * changes the shape of that constant would otherwise retroactively change what this
 * migration writes for a customer still upgrading from 2.x.
 */
return new class extends Migration {

    /**
     * The class names this package wrote before 3.0.0, and the alias each became.
     *
     * @var array<string, string>
     */
    private const LEGACY_CLASSES = [
        'ThreeLeaf\\Biblioteca\\Models\\Paragraph' => 'b_paragraphs',
        'ThreeLeaf\\Biblioteca\\Models\\Sentence' => 'b_sentences',
    ];

    /** The annotation table, named literally for the same reason the classes above are. */
    private const TABLE = 'b_annotations';

    /**
     * Rewrite stored class names to their morph aliases.
     *
     * @return void
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (self::LEGACY_CLASSES as $class => $alias) {
                $this->rewrite($class, $this->aliasFor($class, $alias));
            }
        });
    }

    /**
     * Rewrite stored morph aliases back to their class names.
     *
     * This is the inverse of {@link up()} only for a downgrade to 2.x, which is the one
     * situation it is for. Rolling it back while still running 3.0.0 leaves the code writing
     * and querying aliases against rows holding class names, which silently empties
     * `$paragraph->annotations()`.
     *
     * It is also less conservative than {@link up()} in one respect: it rewrites the
     * package's aliases whoever wrote them, so a host that had repointed `b_paragraphs` at a
     * subclass of its own has those rows restated as the package's class. Such a host should
     * not roll this migration back. Rows are restored in the canonical letter case, so a 2.x
     * row that held a mis-cased class name does not come back byte-identical; it denotes the
     * same class either way.
     *
     * @return void
     */
    public function down(): void
    {
        DB::transaction(function (): void {
            foreach (self::LEGACY_CLASSES as $class => $alias) {
                $this->rewrite($alias, $class);
            }
        });
    }

    /**
     * Decide which alias a class should be rewritten to.
     *
     * The application's morph map is preferred over the frozen alias, because a host that
     * registers an alias of its own for one of these models has that alias written by
     * ordinary model writes and constrained on by `$paragraph->annotations()`. Writing the
     * package alias to a host like that would split the column across two discriminators and
     * lose every pre-upgrade row from the relation — the failure this migration exists to
     * prevent.
     *
     * The frozen alias is the fallback for a process with no morph map registered at all,
     * which is what 3.0.0 will read those rows as.
     *
     * @param string $class The class name stored by earlier releases.
     * @param string $alias The alias this package registers for that class.
     *
     * @return string The value to store.
     */
    private function aliasFor(string $class, string $alias): string
    {
        $registered = Relation::getMorphAlias($class);

        return $registered === $class ? $alias : $registered;
    }

    /**
     * Replace one stored reference type with another, matching case-insensitively.
     *
     * The match ignores letter case because PHP resolves class names that way, so a row
     * holding a differently-cased class name denotes the same model and the model has always
     * accepted it. Comparing with <code>LOWER()</code> rather than a collation keeps the
     * behaviour identical on SQLite, MySQL, and PostgreSQL.
     *
     * A leading backslash is matched as well. Releases before 2.2.0 did not constrain the
     * column, so `\ThreeLeaf\Biblioteca\Models\Paragraph` could be stored verbatim; the model
     * still resolves it, which would leave it readable yet absent from the parent relation.
     *
     * @param string $from The stored value to replace.
     * @param string $to   The value to store instead.
     *
     * @return void
     */
    private function rewrite(string $from, string $to): void
    {
        DB::table(self::TABLE)
            ->whereRaw('LOWER(reference_type) IN (?, ?)', [
                strtolower($from),
                '\\' . strtolower($from),
            ])
            ->update(['reference_type' => $to]);
    }
};
