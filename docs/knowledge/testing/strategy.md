---
type: Testing Strategy
title: Testing Strategy
description: The PHPUnit suites, the compatibility matrix run in CI, and how the coverage badge is produced.
resource: phpunit.xml
tags: [testing, phpunit, ci, coverage]
timestamp: 2026-09-04T00:00:00Z
---

# Testing Strategy

## Suites

[`phpunit.xml`](../../../phpunit.xml) declares two suites:

| Suite     | Directory       | Covers                                                           |
| --------- | --------------- | ---------------------------------------------------------------- |
| `Unit`    | `tests/Unit`    | Traits and utilities with no database — `Equals`, `UuidUtil`     |
| `Feature` | `tests/Feature` | Models, repositories, services, controllers, requests, resources |

Coverage is measured over `./src` only. Reports are written to
`target/coverage` as Clover XML and HTML; `target/` is git-ignored.

Run the suite with the project's configured test command:

```bash
./vendor/bin/phpunit
```

Because this is a package rather than an application, feature tests boot a
minimal Laravel through `orchestra/testbench` rather than a full application
kernel.

## Compatibility matrix

[`.github/workflows/tests.yaml`](../../../.github/workflows/tests.yaml) runs on
push and pull request against `main`, over a two-cell matrix with
`fail-fast: false`:

| PHP   | Laravel |
| ----- | ------- |
| `8.2` | `^12.0` |
| `8.4` | `^13.0` |

Each cell rewrites the `illuminate/database` and `illuminate/support`
constraints with `composer require --no-update`, then runs
`composer update --prefer-dist --no-progress -W`. This proves both supported
Laravel majors resolve and pass, which is what the `^12.0 || ^13.0` constraint
in [`composer.json`](../../../composer.json) claims. Tests run on SQLite —
`pdo_sqlite` is the database extension installed in the workflow.

Because `fail-fast` is off, a break in one Laravel major does not hide the
result for the other.

## Coverage badge

[`util/generate-coverage-badge.php`](../../../util/generate-coverage-badge.php)
reads the first `N% covered` figure out of the generated HTML report — the line
coverage percentage — and writes an SVG badge that the root
[`README.md`](../../../README.md) references.

## What to test when changing this package

- **A new model** needs a feature test for its relationships and a factory in
  `database/factories/`.
- **A new migration column** needs its `comment()` — see
  [Conventions](/style/conventions.md) — and an assertion that the column is
  fillable if it is writable.
- **A new route** needs a feature test covering the success path and the
  validation-failure path, since validation is the package's only enforced
  control. See [Input Validation](/security/input-validation.md).

# Citations

- Verified 2026-09-04 against git HEAD — suite names, directories, and the
  `<source><include>./src</include></source>` block read from `phpunit.xml`.
- Verified 2026-09-04 against git HEAD — the matrix in
  `.github/workflows/tests.yaml` is PHP 8.2 with Laravel `^12.0` and PHP 8.4
  with Laravel `^13.0`, `fail-fast: false`, extensions
  `mbstring, pdo_sqlite, zip, curl`.
- Verified 2026-09-04 against git HEAD — `composer.json` `require-dev` includes
  `orchestra/testbench`.
- Verified 2026-09-04 against git HEAD —
  `util/generate-coverage-badge.php::getCoveragePercentageFromHtml()` matches
  `/>([0-9]+\.[0-9]+)% covered/` and takes the first occurrence.
