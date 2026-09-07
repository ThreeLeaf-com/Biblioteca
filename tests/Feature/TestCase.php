<?php

namespace Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ThreeLeaf\Biblioteca\Providers\BibliotecaServiceProvider;

abstract class TestCase extends OrchestraTestCase
{

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRoutes();
    }

    /**
     * Define the routes required for testing.
     */
    protected function setUpRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../../routes/api.php');
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return array_merge(
            parent::getPackageProviders($app),
            [
                BibliotecaServiceProvider::class,
            ]
        );
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        /* Use SQLite in-memory database for testing.

           foreign_key_constraints makes Laravel issue "PRAGMA foreign_keys=ON" when it
           opens the connection, before RefreshDatabase starts its transaction. SQLite
           ignores the pragma inside a transaction, so this is the only point at which
           it can take effect. See ForeignKeyConstraintTest. */
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }
}
