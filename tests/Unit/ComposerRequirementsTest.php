<?php

namespace Tests\Unit;

use Composer\Semver\Semver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test that `composer.json` declares the runtime packages that the package needs but that
 * `illuminate/database` and `illuminate/support` only suggest.
 *
 * `UuidUtil` uses `symfony/uid`, and the models' `HasUuids` keys use `ramsey/uuid` through
 * `Str`. The test suite alone cannot see a missing requirement, because `orchestra/testbench`
 * installs `laravel/framework`, which requires both packages, while a consumer without
 * `laravel/framework` gets "class not found". This test reads the manifest, not the
 * installed vendor tree, so a dev dependency cannot hide a missing runtime requirement.
 */
class ComposerRequirementsTest extends TestCase
{

    /**
     * Read the `require` block of the package's `composer.json`.
     *
     * @return array<string, string> The package name to version constraint map.
     */
    private static function runtimeRequirements(): array
    {
        $manifest = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true, flags: JSON_THROW_ON_ERROR);

        return $manifest['require'] ?? [];
    }

    /**
     * The runtime packages to check, with the versions their constraint must admit and reject.
     *
     * The admitted versions include the lowest that Laravel 12 and Laravel 13 each require and
     * versions near the top of each allowed major, so neither a raised floor nor a pin to one
     * minor version passes: either would force a Laravel application to change the package.
     *
     * @return array<string, array{string, list<string>, list<string>}> Package name, versions
     *     the constraint must admit, and versions it must reject, keyed by package name.
     */
    public static function runtimePackages(): array
    {
        return [
            'symfony/uid' => ['symfony/uid', ['7.2.0', '7.4.0', '7.99.0', '8.0.0', '8.99.0'], ['7.1.99', '9.0.0']],
            'ramsey/uuid' => ['ramsey/uuid', ['4.7.0', '4.9.4', '4.99.0'], ['4.6.99', '5.0.0']],
        ];
    }

    /** Test that the source still uses each namespace this test guards. */
    #[Test]
    public function guardedNamespacesAreStillUsed(): void
    {
        $uuidUtil = file_get_contents(__DIR__ . '/../../src/Utils/UuidUtil.php');
        $this->assertMatchesRegularExpression('/Symfony\\\\Component\\\\Uid\\\\/', $uuidUtil);

        $tag = file_get_contents(__DIR__ . '/../../src/Models/Tag.php');
        $this->assertMatchesRegularExpression('/\buse HasUuids;/', $tag);
    }

    /**
     * Test that a package is a runtime requirement whose constraint admits the supported versions.
     *
     * @param string       $package  The Composer package name.
     * @param list<string> $admitted Versions the constraint must accept.
     * @param list<string> $rejected Versions the constraint must refuse.
     */
    #[Test]
    #[DataProvider('runtimePackages')]
    public function packageIsRequiredAtRuntime(string $package, array $admitted, array $rejected): void
    {
        $requirements = self::runtimeRequirements();
        $this->assertArrayHasKey($package, $requirements, "$package must be in composer.json require.");

        $constraint = $requirements[$package];
        foreach ($admitted as $version) {
            $this->assertTrue(Semver::satisfies($version, $constraint), "$package $constraint must admit $version.");
        }
        foreach ($rejected as $version) {
            $this->assertFalse(Semver::satisfies($version, $constraint), "$package $constraint must reject $version.");
        }
    }
}
