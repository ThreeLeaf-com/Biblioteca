<?php

namespace ThreeLeaf\Biblioteca\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use ThreeLeaf\Biblioteca\Models\Annotation;

class BibliotecaServiceProvider extends ServiceProvider
{
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

        $this->registerMorphMap();
    }

    /**
     * Alias the models an {@link Annotation} can reference.
     *
     * Without this, the polymorphic <code>reference_type</code> column stores the package's
     * own class names, publishing an internal namespace through the API and tying stored
     * data to a PHP namespace that a rename would invalidate. The aliases are the prefixed
     * table names, so they are already unique to this package.
     *
     * {@link Relation::morphMap()} is deliberate: {@link Relation::enforceMorphMap()} also
     * calls {@link Relation::requireMorphMap()}, a process-global flag that would make every
     * unmapped morph in the *host* application throw. A package must not impose that on the
     * application that installs it.
     *
     * The merge is <code>$map + static::$morphMap</code> and application providers boot
     * after package providers, so a host that registers either alias for one of its own
     * models silently repoints it, and <code>morphMap($map, false)</code> removes these
     * entries entirely. Laravel detects neither. The upgrade notes state the alias namespace
     * this package claims; detecting a collision here is not possible without the
     * process-global flag above.
     *
     * @return void
     */
    private function registerMorphMap(): void
    {
        Relation::morphMap(Annotation::REFERENCE_TYPES);
    }
}
