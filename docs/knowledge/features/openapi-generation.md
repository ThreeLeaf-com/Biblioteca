---
type: Feature
title: OpenAPI Generation
description: How target/api-docs.json is produced from source annotations, and when it runs.
resource: util/generate-open-api.php
tags: [feature, openapi, swagger, composer]
timestamp: 2026-09-04T00:00:00Z
---

# OpenAPI Generation

The package's HTTP contract is described by `@OA\*` annotations in the source
and compiled into a single OpenAPI document.

## The generator

[`util/generate-open-api.php`](../../../util/generate-open-api.php) scans two
paths — its own file and [`src/`](../../../src) — with `OpenApi\Generator` and
`OpenApi\SourceFinder`, then writes the JSON to `target/api-docs.json`,
creating `target/` at mode `0755` if it is missing. It prints the scanned paths
and the number of paths found, so a run that silently produced an empty document
is visible.

The script declares an `OpenApiSpec` class carrying
`@OA\Info(title="Generated API", version="1.0")`. That class exists only to hold
the annotation; the generator requires a top-level `Info` block.

Failures are caught and reported as text — the script prints the exception
message rather than exiting non-zero. **A generation failure will not fail a
build that only checks the exit code.**

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
not regenerate it.

`target/` is git-ignored, which means the generated document is a build
artifact, not a committed file. The annotations in the source are the artifact
under version control.

## What gets annotated

- **Models** — `@OA\Schema` with a `@OA\Property` per column and per eager-loaded
  relationship.
- **Form requests** — `@OA\Schema` describing the request body, with `required`
  and `example` values.
- **Resources** — `@OA\Schema` describing the response body.
- **Controllers** — `@OA\Tag` on the class, namespaced `Biblioteca/<Entity>`,
  and `@OA\Get` / `@OA\Post` / `@OA\Put` / `@OA\Delete` on the methods.
- **Enums** — `@OA\Schema` with an `enum` list, so `NoteType` and `Context`
  appear as named string schemas.

Because the annotations live beside the code they describe, changing a column or
a validation rule without updating its annotation leaves the specification
wrong with no build failure. See [Conventions](/style/conventions.md) and
[REST Endpoints](/api/rest-endpoints.md).

## Dependency

`darkaonline/l5-swagger` (`^9.0 || ^11.0`) is a `require-dev` dependency. The
generator is a development tool; consumers of the package do not need it.

# Citations

- Verified 2026-09-04 against git HEAD — `util/generate-open-api.php` scans
  `[__FILE__, __DIR__ . '/../src']`, writes `../target/api-docs.json`, and
  catches `Exception` without re-throwing.
- Verified 2026-09-04 against git HEAD — `composer.json` declares
  `scripts.post-install-cmd` as `["@php util/generate-open-api.php"]` and no
  `post-update-cmd`.
- Verified 2026-09-04 against git HEAD — `.gitignore` lists `target/`.
- Verified 2026-09-04 against git HEAD — `darkaonline/l5-swagger` is in
  `require-dev` at `^9.0 || ^11.0`.
- Verified 2026-09-04 against git HEAD — `Context` and `NoteType` each carry an
  `@OA\Schema` with an `enum` list.
