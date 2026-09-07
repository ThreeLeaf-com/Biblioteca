---
type: Style Guide
title: Conventions
description: Naming, PHPDoc, annotation, and migration-comment rules this package follows.
resource: src
tags: [style, conventions, phpdoc]
timestamp: 2026-09-06T00:00:00Z
---

# Conventions

The conventions below are the ones a reader of this codebase will notice first,
and the ones a contributor is expected to follow.

## Naming

- **Namespace root** is `ThreeLeaf\Biblioteca\`, mapped to `src/` by PSR-4.
- **Tables** carry the `b_` prefix from `BibliotecaConstants::TABLE_PREFIX`, and
  each model exposes its own `TABLE_NAME` constant. Reference the constant, not
  the literal, wherever a table name is needed — validation rules do this.
  - **Exception: `Paragraph::TABLE_NAME` and `Sentence::TABLE_NAME` are also
    morph aliases**, and a morph alias is persisted data. Renaming either table
    would change what `Annotation.reference_type` stores and orphan every
    existing row. A test pins both to their literal values so a rename fails
    loudly; treat it as a data-format change requiring its own migration, not a
    rename. See [Domain Model](/data/models/domain-model.md).
  - **Migrations are the other exception**: they name tables and classes as
    literals, because a migration is replayed by every fresh install and must
    keep doing what it did when it was written.
- **Primary keys** are named after the entity (`book_id`, `chapter_id`,
  `toc_id`), never `id`.
- **Route parameters** match the key name: `books/{book_id}`.

## PHPDoc

Every class carries a docblock. Beyond that:

- Models declare the full `@property` and `@property-read` block for every
  column and relationship, with `@property Carbon` for date columns and
  `@mixin Builder` on the class.
- Methods document `@param`, `@return`, and `@throws` with prose, not just
  types — repository read methods, for example, state which variant returns
  `null` and which throws `ModelNotFoundException`.
- Cross-references use the `{@link ClassName}` form.
- Traits document their contract, including any property the using class is
  expected to declare — `HasCompositeKey` documents `$primaryKeys`.

## OpenAPI attributes

Models, form requests, resources, controllers, and enums carry `#[OA\...]`
attributes below their PHPDoc, imported as `use OpenApi\Attributes as OA;`.
Controller tags are namespaced `Biblioteca/<Entity>`. These attributes are
load-bearing, not decorative: they are the input to
[OpenAPI Generation](/features/openapi-generation.md).

Do **not** write the docblock `@OA\*` form. swagger-php reads it only when
`doctrine/annotations` is installed, which this package does not do, so a
docblock annotation is silently ignored.

## Migration comments

Every table gets `$table->comment(...)` and **every column** gets
`->comment(...)`. The comment mirrors the intent recorded in the model's PHPDoc,
so the schema is self-describing when read through a database client rather than
through the code. Timestamps use the fixed form:

```php
$table->timestamp(Model::CREATED_AT)->useCurrent()->comment('...');
$table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('...');
```

See [Database Schema](/data/models/database-schema.md).

## Validation rules

Rules are arrays of strings, not pipe-delimited strings, so a rule object such
as `Rule::unique()` can sit alongside plain rules. See
[Input Validation](/security/input-validation.md).

## Dependencies

Services and repositories are injected as promoted `private readonly`
constructor properties. See [Layering](/architecture/layering.md).

## HTTP status codes

Controllers import
`Symfony\Component\HttpFoundation\Response as HttpCodes` and use its constants
rather than integer literals.

## Documentation

Markdown in this bundle follows OKF conventions: bundle-root-relative links
between concepts, relative paths to in-repo files outside the bundle, and a
`# Citations` section recording what was verified and when. Terms are defined in
[Glossary](/style/glossary.md).

# Citations

- Verified 2026-09-04 against git HEAD — PSR-4 root and namespace read from
  `composer.json`; `BibliotecaConstants::TABLE_PREFIX` is `'b_'`.
- Verified 2026-09-04 against git HEAD — `Book` carries the `@property` block,
  `@mixin Builder`, and `@OA\Schema`; `HasCompositeKey` documents `$primaryKeys`.
- Verified 2026-09-04 against git HEAD — every `Schema::create` block in the
  migration calls `$table->comment()` and comments each column.
- Verified 2026-09-04 against git HEAD — `AuthorController` imports
  `Symfony\Component\HttpFoundation\Response as HttpCodes`.
