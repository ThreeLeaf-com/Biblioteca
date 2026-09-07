---
type: Testing Strategy
title: Testing Strategy
description: The PHPUnit suites, the compatibility matrix run in CI, how the coverage badge is produced, how to verify tests by mutation, and why foreign keys are not enforced in feature tests.
resource: phpunit.xml
tags: [testing, phpunit, ci, coverage, mutation, transactions]
timestamp: 2026-09-06T00:00:00Z
---

# Testing Strategy

## Suites

[`phpunit.xml`](../../../phpunit.xml) declares two suites:

| Suite     | Directory       | Covers                                                           |
| --------- | --------------- | ---------------------------------------------------------------- |
| `Unit`    | `tests/Unit`    | Traits, utilities, and the generated OpenAPI document — no database |
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
- **A new endpoint or schema** needs its path or schema name asserted in
  `tests/Unit/OpenApi/OpenApiDocumentTest.php`, which generates the OpenAPI
  document from `src/` and fails when it is empty or when a representative
  attribute stops being read. See
  [OpenAPI Generation](/features/openapi-generation.md).

## Verify service and repository tests by mutation

A test that passes against a gutted implementation protects nothing, and it
reads as coverage. Confirm the test by **removing the behaviour and watching the
test fail**, rather than by reading the code:

1. Delete or neutralise the behaviour under test.
2. Run the test. It must fail, and the failure message must name the behaviour
   you removed.
3. Restore the implementation and confirm the suite is green again.

Three shapes of test in this package go wrong without that check:

- **Transaction rollback.** Assert on state re-read from the database, and
  inject the failure at a point where earlier writes have already run. A failure
  raised before any rows are written tests an empty transaction, and passes with
  the transaction removed.
- **Persistence.** Assert against a re-read row, not the in-memory model.
  `$model->only(...)` on the instance you just passed in cannot tell
  `update()` from `fill()`, because both leave the same attributes in memory.
- **Absence.** `assertDatabaseMissing` is also true when the code under test did
  nothing at all. Pair it with proof that the work was attempted — catch the
  specific injected failure and assert it fired.

Catch the narrowest exception type you can. `PHPUnit\Framework\AssertionFailedError`
and `Illuminate\Database\QueryException` both extend `RuntimeException`, so a
`catch (RuntimeException)` swallows assertion failures and real database errors
alike, and hides the defects the test exists to detect.

## How to inject a failure

Prefer a **collaborator seam**: subclass the dependency and make it fail. See
[`ReparseTransactionTest`](../../../tests/Feature/Services/ReparseTransactionTest.php),
where `FailingParagraphService` stands in for a mid-rebuild error. This reads
clearly and survives refactoring.

Fall back to a **query listener** only when the code under test has no
injectable collaborator.
[`SeriesServiceTest::failOnInsertInto()`](../../../tests/Feature/Services/SeriesServiceTest.php)
is the worked example: `SeriesService` takes no constructor dependencies, and
`BelongsToMany::attach()` writes through the pivot query builder without firing
Eloquent model events, so no cheaper seam exists. Anchor the match on the insert
target — `b_series` is a prefix of `b_series_books`, so a substring test matches
the wrong table.

## Known defect: foreign keys are not enforced in feature tests

Tracked as [#27](https://github.com/ThreeLeaf-com/Biblioteca/issues/27). **Remove
this section when that issue closes.**

[`Tests\Feature\TestCase::setUp()`](../../../tests/Feature/TestCase.php) issues
`PRAGMA foreign_keys=ON`, but `RefreshDatabase` has already opened a transaction
by then, and SQLite treats that pragma as a no-op inside a transaction.
Referential integrity is therefore **not enforced** in any feature test.

Until that is fixed, a test cannot use an unknown foreign key as its failure
trigger: the write succeeds and the test passes regardless.

# Citations

- Verified 2026-09-06 by execution — with `SeriesService`'s two
  `DB::transaction()` calls removed and `ChapterRepository::update()` changed to
  `fill()` without saving, `SeriesServiceTest` and `ChapterRepositoryTest` at
  `staging` reported `OK (19 tests, 33 assertions)`. Each mutant applied
  separately against the tests on this branch kills exactly one test.
- Verified 2026-09-06 by execution — `PHPUnit\Framework\AssertionFailedError`
  and `Illuminate\Database\QueryException` both report `true` for
  `is_subclass_of(..., 'RuntimeException')`.
- Verified 2026-09-06 by execution — a probe test using `RefreshDatabase`
  reports `PRAGMA foreign_keys` as `0` with `DB::transactionLevel()` as `1`
  after `Tests\Feature\TestCase::setUp()` has run.
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
