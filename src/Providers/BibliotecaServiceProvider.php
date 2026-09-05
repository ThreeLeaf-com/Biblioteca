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
     * The merge is <code>$map + static::$morphMap</code>, which prepends the caller's
     * entries, and {@link \Illuminate\Database\Eloquent\Model::getMorphClass()} takes the
     * first match — so the *last* registration wins. A host that registers its map from a
     * service provider's <code>boot()</code> therefore takes precedence over this one,
     * because package providers boot first. A host that registers from
     * <code>register()</code> or from <code>bootstrap/app.php</code> runs *before* this
     * method and does not: this package's entries are prepended over the host's, and a model
     * the host had been persisting under its own discriminator starts reporting the
     * package's.
     *
     * A host that claims either of these aliases for one of its own models silently
     * repoints it, and <code>morphMap($map, false)</code> removes these entries entirely.
     * Laravel detects neither. The upgrade notes state the alias namespace this package
     * claims; detecting a collision here is not possible without the process-global flag
     * above.
     *
     * Registering here rather than in <code>register()</code> is deliberate: it is what
     * gives a host's own <code>boot()</code> the last word.
     *
     * @return void Nothing is returned; the map is registered on a static in Relation.
     */
    private function registerMorphMap(): void
    {
        Relation::morphMap(Annotation::REFERENCE_TYPES);
    }
}
