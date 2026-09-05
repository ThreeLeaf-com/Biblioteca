<?php

namespace ThreeLeaf\Biblioteca\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use ThreeLeaf\Biblioteca\Models\Annotation;
use ThreeLeaf\Biblioteca\Models\Paragraph;
use ThreeLeaf\Biblioteca\Models\Sentence;

class BibliotecaServiceProvider extends ServiceProvider
{

    /**
     * The morph map for the Biblioteca polymorphic relations.
     *
     * {@link Annotation::reference()} is a morphTo relation, so the <code>reference_type</code>
     * column decides which class Eloquent resolves. Without a map that column holds a
     * fully-qualified class name, which exposes internal class names through the API and
     * leaves the stored value as the only thing between a caller and an arbitrary class
     * lookup.
     *
     * The aliases are the prefixed table names, so they cannot collide with a map the host
     * application has already registered.
     *
     * @var array<string, class-string<Model>>
     */
    public const MORPH_MAP = [
        Paragraph::TABLE_NAME => Paragraph::class,
        Sentence::TABLE_NAME => Sentence::class,
    ];

    public function register(): void
    {
        /* Register bindings in the container if necessary. */
    }

    /**
     * This method is responsible for bootstrapping the service provider.
     * It is called after all other service providers have been registered.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public function boot(): void
    {
        /* Include the required database migration scripts */
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        /*
         * Register, but do not enforce, the morph map. Relation::enforceMorphMap() also
         * calls Relation::requireMorphMap(), which sets a process-global flag that makes
         * getMorphClass() throw for every unmapped model in the host application as well.
         * A package must not impose that on its host, so the map is merged in and the
         * allow-list in AnnotationRequest does the actual gate-keeping.
         */
        Relation::morphMap(self::MORPH_MAP);
    }
}
