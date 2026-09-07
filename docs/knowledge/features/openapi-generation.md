---
type: Feature
title: OpenAPI Generation
description: How target/api-docs.json is produced from source attributes, why attributes rather than docblock annotations, and when it runs.
resource: util/generate-open-api.php
tags: [feature, openapi, swagger, composer, attributes]
timestamp: 2026-09-07T00:00:00Z
---

# OpenAPI Generation

The package's HTTP contract is described by `#[OA\...]` PHP attributes in the
source and compiled into a single OpenAPI document.

## Attributes, not docblock annotations

The package uses **PHP attributes**. Docblock `@OA\*` annotations describe the
same document, but swagger-php reads them only when
`Doctrine\Common\Annotations\DocParser` is on the autoloader —
`OpenApi\Analysers\DocBlockParser::isEnabled()` is a `class_exists()` check on
that class. `doctrine/annotations` is not a dependency of this package, and
Composer reports it as abandoned, so it will not become one.

Attributes are read by `OpenApi\Analysers\AttributeAnnotationFactory`, which
needs no dependency beyond swagger-php itself. That, not longevity, is the
reason for the choice: swagger-php's roadmap marks the whole classic pipeline —
annotations **and** attributes — deprecated in v7 in favour of the new `Spec`
system, and removes classic entirely in v8. Both forms are therefore on the same
timeline. Attributes are simply the only classic form that works today without
an abandoned dependency, and the migration to `Spec` will start from them.

This package used docblock annotations until 3.0.1. They stopped being read
when the docblock path became conditional, and every annotation in the package
— 653 lines across 54 files — went inert, producing `{"openapi":"3.0.0"}` with
no paths and no schemas. The migration was verified by generating the document
with `doctrine/annotations` temporarily installed and diffing the attribute-era
document against it: the two are identical, at 28 paths and 42 schemas.

Two errors the annotations had always contained became visible once the document
was generated again, and were corrected in the same release.
`AnnotationController::update()` declared `/api/annotations/{id}` while every
other annotation operation declared `/api/annotations/{annotation_id}`, so one
resource was published as two unrelated path entries. A path parameter is a
placeholder, so a generated client still called the right URL; the damage was to
the document, which showed no update operation on the path a reader would look
at. `LibraryController::index()` referenced a `Biblioteca` tag that no class
declares, so its own `Biblioteca/Library` tag was dropped and a description-less
one synthesised in its place. The first correction merges the two path entries,
so the document now has **27 paths and 42 schemas**.

## The generator

[`util/generate-open-api.php`](../../../util/generate-open-api.php) scans two
paths — its own file and [`src/`](../../../src) — with `OpenApi\Generator` and
`OpenApi\SourceFinder`, then writes the JSON to `target/api-docs.json`,
creating `target/` at mode `0755` if it is missing. It prints the scanned paths
and the number of paths and schemas found.

The script declares an `OpenApiSpec` class carrying
`#[OA\Info(title: 'Generated API', version: '1.0')]`. That class exists only to
hold the attribute; the generator requires a top-level `Info` block.

**An incomplete document fails the build.** The script exits `1` when the
generated document has zero paths, zero schemas, or no `Info` block, and exits
`1` on a `Throwable` rather than printing the message and returning success. The
check runs before the document is written and before anything reports success,
so a failing run leaves no plausible-looking artifact behind a success banner.
That is the guard against a repeat of the inert-annotation failure, which was
invisible precisely because the generator always exited `0`.

Absent members of the generated document are swagger-php's
`OpenApi\Generator::UNDEFINED` sentinel — a string, not `null` — so the checks
use `Generator::isDefault()` rather than a null comparison.

`tests/Unit/OpenApi/OpenApiDocumentTest.php` makes the same guarantee from the
test suite, where a reviewer sees it: it generates from `src/` and asserts the
document has paths, has schemas, and covers a representative endpoint and a
representative model, form request, and API resource schema. It generates with
validation off, because `src/` alone carries no `OA\Info` — that lives in the
build script, which cannot be scanned from a test without executing it.

## When it runs

[`composer.json`](../../../composer.json) wires it to both Composer script
hooks:

```json
"scripts": {
    "post-install-cmd": [
        "@php util/generate-open-api.php"
    ],
    "post-update-cmd": [
        "@php util/generate-open-api.php"
    ]
}
```

Both hooks are wired, so `composer install` and `composer update` each
regenerate the document and each fail on an incomplete one. That matters for CI:
`.github/workflows/tests.yaml` installs with `composer update`, so without the
`post-update-cmd` hook the gate would never run there and could be reverted
without breaking anything.

`target/` is git-ignored, which means the generated document is a build
artifact, not a committed file. The attributes in the source are the artifact
under version control.

## What gets annotated

- **Models** — `#[OA\Schema]` with an `OA\Property` per column and per
  eager-loaded relationship.
- **Form requests** — `#[OA\Schema]` describing the request body, with
  `required` and `example` values.
- **Resources** — `#[OA\Schema]` describing the response body.
- **Controllers** — `#[OA\Tag]` on the class, namespaced `Biblioteca/<Entity>`,
  and `#[OA\Get]` / `#[OA\Post]` / `#[OA\Put]` / `#[OA\Delete]` on the methods.
- **Enums** — `#[OA\Schema]` with an `enum` list, so `NoteType` and `Context`
  appear as named string schemas.

Each file imports the attribute namespace as `use OpenApi\Attributes as OA;`.
Nested constructs are objects rather than positional annotations: an operation
takes `responses: [new OA\Response(...)]` and `parameters: [new OA\Parameter(...)]`,
a schema takes `properties: [new OA\Property(...)]`, and a response or request
body takes `content: new OA\JsonContent(...)`.

Because the attributes live beside the code they describe, changing a column or
a validation rule without updating its attribute leaves the specification
wrong with no build failure. The build gate catches a document that is empty,
not one that is merely stale. See [Conventions](/style/conventions.md) and
[REST Endpoints](/api/rest-endpoints.md).

## Dependency

`darkaonline/l5-swagger` (`^9.0 || ^11.0`) is a `require-dev` dependency and
brings in `zircote/swagger-php`. The generator is a development tool; consumers
of the package do not need it to use the models, and normal runtime never
touches the attributes — PHP resolves an attribute class only when something
calls `ReflectionAttribute::newInstance()`.

The one case that needs `zircote/swagger-php` present is a consumer that
reflects over this package's classes and instantiates their attributes — an
attribute-scanning documentation or metadata tool pointed at `vendor/`. Such a
tool must require swagger-php itself; the package does not promote it to
`require` for everyone else's benefit. The docblock form had no equivalent
coupling, because an unread docblock is inert by definition.

# Citations

- Verified 2026-09-07 against git HEAD — `util/generate-open-api.php` scans
  `[__FILE__, __DIR__ . '/../src']`, writes `../target/api-docs.json`, and exits
  `1` on a `Throwable` or on a document with zero paths, zero schemas, or no
  `Info` block, before writing the document or reporting success.
- Verified 2026-09-07 against git HEAD — no `@OA\` docblock annotation remains
  in `src/`; every annotated file imports `OpenApi\Attributes as OA`.
- Verified 2026-09-07 by generation — the document produced from the attributes
  is identical to the one produced from the previous docblock annotations with
  `doctrine/annotations` installed: 28 paths, 42 schemas. After the
  `AnnotationController` path correction the document has 27 paths and 42
  schemas.
- Verified 2026-09-07 against swagger-php 6.7.1 —
  `OpenApi\Analysers\DocBlockParser::isEnabled()` returns
  `class_exists('Doctrine\Common\Annotations\DocParser')`, and `ROADMAP.md`
  states that v7 deprecates "all classic code - annotations + attributes and
  related pipeline code" and that v8 removes classic from the codebase.
- Verified 2026-09-07 by `composer require --dev doctrine/annotations` —
  Composer reports "Package doctrine/annotations is abandoned".
- Verified 2026-09-07 against git HEAD — `composer.json` declares both
  `scripts.post-install-cmd` and `scripts.post-update-cmd` as
  `["@php util/generate-open-api.php"]`; `.github/workflows/tests.yaml` installs
  with `composer update`, which fires the latter.
- Verified 2026-09-07 against git HEAD — `.gitignore` lists `target/`.
- Verified 2026-09-07 against git HEAD — `darkaonline/l5-swagger` is in
  `require-dev` at `^9.0 || ^11.0`.
- Verified 2026-09-07 against git HEAD — `Context` and `NoteType` each carry an
  `#[OA\Schema]` with an `enum` list.
- Verified 2026-09-07 against git HEAD — `routes/api.php` binds
  `annotations/{annotation_id}` on the update route, and no class declares an
  `OA\Tag` named `Biblioteca`.
