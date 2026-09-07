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
needs no dependency beyond swagger-php itself. swagger-php's roadmap deprecates
the classic annotation path in v7 and removes it in v8, so attributes are also
where the library is going.

This package used docblock annotations until 3.0.1. They stopped being read
when the docblock path became conditional, and every annotation in the package
— 653 lines across 54 files — went inert, producing `{"openapi":"3.0.0"}` with
no paths and no schemas. The migration was verified by generating the document
with `doctrine/annotations` temporarily installed and diffing the attribute-era
document against it: the two are identical.

## The generator

[`util/generate-open-api.php`](../../../util/generate-open-api.php) scans two
paths — its own file and [`src/`](../../../src) — with `OpenApi\Generator` and
`OpenApi\SourceFinder`, then writes the JSON to `target/api-docs.json`,
creating `target/` at mode `0755` if it is missing. It prints the scanned paths
and the number of paths and schemas found.

The script declares an `OpenApiSpec` class carrying
`#[OA\Info(title: 'Generated API', version: '1.0')]`. That class exists only to
hold the attribute; the generator requires a top-level `Info` block.

**An empty document fails the build.** The script exits `1` when the generated
document has zero paths or zero schemas, and exits `1` on a `Throwable` rather
than printing the message and returning success. That is the guard against a
repeat of the inert-annotation failure, which was invisible precisely because
the generator always exited `0`.

`tests/Unit/OpenApi/OpenApiDocumentTest.php` makes the same guarantee in CI,
where the generator does not run: it generates from `src/` and asserts the
document has paths, has schemas, and covers a representative endpoint and a
representative model, form request, and API resource schema. It generates with
validation off, because `src/` alone carries no `OA\Info` — that lives in the
build script, which cannot be scanned from a test without executing it.

## When it runs

[`composer.json`](../../../composer.json) wires it to the `post-install-cmd`
script hook:

```json
"scripts": {
    "post-install-cmd": [
        "@php util/generate-open-api.php"
    ]
}
```

So `composer install` regenerates the document. Note the hook is on
`post-install-cmd` only, not `post-update-cmd`, so a bare `composer update` does
not regenerate it — and the CI workflow runs `composer update`, so the generator
is not exercised there. The unit test is what covers CI.

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
of the package do not need it.

# Citations

- Verified 2026-09-07 against git HEAD — `util/generate-open-api.php` scans
  `[__FILE__, __DIR__ . '/../src']`, writes `../target/api-docs.json`, and exits
  `1` on a `Throwable` or on a document with zero paths or zero schemas.
- Verified 2026-09-07 against git HEAD — no `@OA\` docblock annotation remains
  in `src/`; every annotated file imports `OpenApi\Attributes as OA`.
- Verified 2026-09-07 by generation — the document produced from the attributes
  is identical to the one produced from the previous docblock annotations with
  `doctrine/annotations` installed: 28 paths, 42 schemas.
- Verified 2026-09-07 against swagger-php 6.7.1 —
  `OpenApi\Analysers\DocBlockParser::isEnabled()` returns
  `class_exists('Doctrine\Common\Annotations\DocParser')`, and `ROADMAP.md`
  states annotations are deprecated in v7 and removed in v8.
- Verified 2026-09-07 by `composer require --dev doctrine/annotations` —
  Composer reports "Package doctrine/annotations is abandoned".
- Verified 2026-09-07 against git HEAD — `composer.json` declares
  `scripts.post-install-cmd` as `["@php util/generate-open-api.php"]` and no
  `post-update-cmd`; `.github/workflows/tests.yaml` runs `composer update`.
- Verified 2026-09-07 against git HEAD — `.gitignore` lists `target/`.
- Verified 2026-09-07 against git HEAD — `darkaonline/l5-swagger` is in
  `require-dev` at `^9.0 || ^11.0`.
- Verified 2026-09-07 against git HEAD — `Context` and `NoteType` each carry an
  `#[OA\Schema]` with an `enum` list.
