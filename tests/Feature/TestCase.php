<?php

namespace Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
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

        if (DB::connection()->getDriverName() === 'sqlite') {
            /* FIXME(#27): This is a no-op. RefreshDatabase has already opened a transaction
               by this point, and SQLite ignores the pragma inside one, so foreign keys are
               not enforced in any feature test. Set foreign_key_constraints on the testing
               connection instead, which applies at connect time. */
            DB::statement('PRAGMA foreign_keys=ON;');
        }
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
        /* Use SQLite in-memory database for testing. */
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
