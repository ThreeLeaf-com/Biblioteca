<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test that `composer.json` declares the runtime packages that the package source imports.
 *
 * Issue #41: `UuidUtil` imports `Symfony\Component\Uid\Uuid`, but `symfony/uid` was not
 * in `require`. The test suite could not see it, because `orchestra/testbench` installs
 * `laravel/framework`, which requires `symfony/uid`. A consumer without
 * `laravel/framework` got "class not found". This test reads the manifest, not the
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

    /** Test that `symfony/uid`, which `UuidUtil` imports, is a runtime requirement. */
    #[Test]
    public function symfonyUidIsRequiredAtRuntime(): void
    {
        $source = file_get_contents(__DIR__ . '/../../src/Utils/UuidUtil.php');
        $this->assertStringContainsString('use Symfony\Component\Uid\Uuid;', $source);

        $this->assertArrayHasKey('symfony/uid', self::runtimeRequirements());
    }

    /**
     * Test that the `symfony/uid` constraint admits the majors that Laravel 12 and 13 install.
     *
     * Laravel 12 requires `^7.2` and Laravel 13 requires `^7.4 || ^8.0`. A narrower range
     * would force a Laravel 12 application on symfony 7.2 or 7.3 to upgrade.
     */
    #[Test]
    public function symfonyUidConstraintCoversSupportedLaravelMajors(): void
    {
        $constraint = self::runtimeRequirements()['symfony/uid'] ?? '';

        $alternatives = array_map('trim', explode('||', $constraint));
        $this->assertContains('^7.2', $alternatives);
        $this->assertContains('^8.0', $alternatives);
    }
}
