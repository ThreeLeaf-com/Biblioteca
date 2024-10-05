<?php

namespace ThreeLeaf\Biblioteca\Providers;

use Illuminate\Support\ServiceProvider;

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
     */
    public function boot(): void
    {
        /* Include the required database migration scripts */
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}