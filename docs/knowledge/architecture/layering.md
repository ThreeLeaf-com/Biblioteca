---
type: Architecture
title: Layering
description: The controller, service, repository, and model layers of the package, and which entities pass through each.
resource: src/Services/ChapterService.php
tags: [architecture, layering, eloquent]
timestamp: 2026-09-04T00:00:00Z
---

# Layering

The package uses four layers. Not every entity uses all four, and that
asymmetry is deliberate rather than incomplete.

```
HTTP request
  → Http/Requests/*Request      (validation)
  → Http/Controllers/Api/*      (transport)
  → Services/*Service           (only where behaviour exists)
  → Repositories/*Repository    (only where behaviour exists)
  → Models/*                    (Eloquent)
  → Http/Resources/*Resource    (serialization)
HTTP response
```

## The model layer covers every entity

One Eloquent model per entity in [`src/Models/`](../../../src/Models) — 15
entity models plus 3 pivot models. The entity models carry the PHPDoc
`@property` block and the OpenAPI `@OA\Schema` annotation; the two `Pivot`
subclasses carry neither. See [Domain Model](/data/models/domain-model.md).

## The HTTP layer covers 11 of the 15 entities

`Bibliography`, `Index`, `Note`, and `TableOfContents` have **no** form request,
no resource, no controller, and no route. They are reachable only through
Eloquent. The other eleven each have all three:

**Form requests** in [`src/Http/Requests/`](../../../src/Http/Requests) hold the
validation rules. See [Input Validation](/security/input-validation.md).

**Resources** in [`src/Http/Resources/`](../../../src/Http/Resources) define the
response shape.

**Controllers** in
[`src/Http/Controllers/Api/`](../../../src/Http/Controllers/Api), plus
`LibraryController` for the aggregate `library` endpoint. See
[REST Endpoints](/api/rest-endpoints.md).

## Layers that exist only where there is behaviour

**Repositories** exist for `Chapter` and `Paragraph` only
([`src/Repositories/`](../../../src/Repositories)). They wrap the Eloquent
calls and add read variants that throw `ModelNotFoundException` instead of
returning `null`.

**Services** exist for `Chapter`, `Paragraph`, and `Series` only
([`src/Services/`](../../../src/Services)). A service is added when creating or
updating an entity has a side effect beyond the write itself. `ChapterService`,
for example, assigns the chapter number and then parses the chapter contents
into paragraphs — see [Chapter Text Parsing](/features/chapter-text-parsing.md).

**The service layer is thinner than the diagram suggests.** Only
`ChapterController` and `SeriesController` inject a service. Every other
controller calls Eloquent directly, for example `Author::all()` in
[`AuthorController::index()`](../../../src/Http/Controllers/Api/AuthorController.php)
— and so do parts of `BookController`, `ParagraphController`, and
`SentenceController`.

`ParagraphService` in particular has **no controller caller at all**: its
`parseParagraphContents()` is invoked from
[`ChapterService::parseChapterContents()`](../../../src/Services/ChapterService.php).
A write to a paragraph through `ParagraphController` therefore does not re-parse
its sentences; only a write to the parent chapter does. See
[Chapter Text Parsing](/features/chapter-text-parsing.md).

## Dependency injection

Services and repositories are constructor-injected as `private readonly`
promoted properties. Laravel's container resolves them; the service provider
registers no explicit bindings.

## Cross-cutting traits

- [`Equals`](../../../src/Traits/Equals.php) — value comparison for models.
- [`HasCompositeKey`](../../../src/Traits/HasCompositeKey.php) — save and update
  support for a primary key made of several columns. Used by `SeriesBook` only,
  which extends `Model`. `BookTag` and `BookGenre` extend Laravel's `Pivot`
  instead, which already handles a two-column key.

# Citations

- Verified 2026-09-04 against git HEAD — `src/Repositories/` contains only
  `ChapterRepository.php` and `ParagraphRepository.php`; `src/Services/` contains
  only `ChapterService.php`, `ParagraphService.php`, and `SeriesService.php`.
- Verified 2026-09-04 against git HEAD — `ChapterService::create()` calls
  `assignChapterNumber()`, `chapterRepository->create()`, then
  `parseChapterContents()`.
- Verified 2026-09-04 against git HEAD — `AuthorController::index()` calls
  `Author::all()` with no service or repository in between; only
  `ChapterController` and `SeriesController` inject a service.
- Verified 2026-09-04 against git HEAD — `src/Http/Requests/`,
  `src/Http/Resources/`, and the entity controllers in
  `src/Http/Controllers/Api/` each hold 11 classes against 15 entity models;
  `Bibliography`, `Index`, `Note`, and `TableOfContents` have none.
- Verified 2026-09-04 against git HEAD — `ParagraphService::parseParagraphContents()`
  is called from `ChapterService::parseChapterContents()` and from no controller.
- Verified 2026-09-04 against git HEAD — `SeriesBook` uses `HasCompositeKey`;
  `BookTag` and `BookGenre` extend `Pivot` and use no trait.
- Verified 2026-09-04 against git HEAD — `ChapterRepository` exposes both
  `read()` (nullable) and a variant documented to throw `ModelNotFoundException`.
