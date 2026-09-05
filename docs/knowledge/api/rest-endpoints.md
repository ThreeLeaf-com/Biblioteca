---
type: API Endpoint Group
title: REST Endpoints
description: The example API route file, the resource endpoints it declares, and the request and response contracts behind them.
resource: routes/api.php
tags: [api, rest, routes, openapi]
timestamp: 2026-09-04T00:00:00Z
---

# REST Endpoints

[`routes/api.php`](../../../routes/api.php) declares the package's HTTP surface.
Its own header comment states that the file **is an example only** — the host
application decides whether to load it and what middleware to wrap it in. See
[Authorization Boundary](/security/authorization-boundary.md).

## Resource groups

Eleven entities expose the same five-route shape, where `{entity}` is one of
`authors`, `books`, `publishers`, `series`, `tags`, `genres`, `chapters`,
`paragraphs`, `sentences`, `figures`, or `annotations`:

| Method   | Path            | Route name         |
| -------- | --------------- | ------------------ |
| `GET`    | `{entity}`      | `{entity}.index`   |
| `GET`    | `{entity}/{id}` | `{entity}.show`    |
| `POST`   | `{entity}`      | `{entity}.store`   |
| `PUT`    | `{entity}/{id}` | `{entity}.update`  |
| `DELETE` | `{entity}/{id}` | `{entity}.destroy` |

Path parameters are named after the entity key, not `id` — `authors/{author_id}`,
`chapters/{chapter_id}`. The `genres` routes are the one inconsistency: their
show, update, and destroy paths bind `{tag_id}` rather than `{genre_id}`. The
binding still works because the parameter is read positionally, but the name is
misleading.

`series` has no plural form, so its index and its show route share the `series`
segment: `GET series` and `GET series/{series_id}`.

## Non-resource routes

| Method   | Path                                | Route name          | Purpose                                    |
| -------- | ----------------------------------- | ------------------- | ------------------------------------------ |
| `GET`    | `library`                           | `library.index`     | Returns `series_ids` and `book_ids` arrays |
| `POST`   | `books/{book_id}/tags`              | `books.addTags`     | Attaches tags to a book                    |
| `DELETE` | `books/{book_id}/tags/{tag_id}`     | `books.removeTag`   | Detaches one tag                           |
| `POST`   | `books/{book_id}/genres`            | `books.addGenres`   | Attaches genres to a book                  |
| `DELETE` | `books/{book_id}/genres/{genre_id}` | `books.removeGenre` | Detaches one genre                         |

`library.index` is the only aggregate endpoint. It is served by
[`LibraryController`](../../../src/Http/Controllers/Api/LibraryController.php),
which plucks the two id columns and returns them as a JSON object — a cheap
manifest for a client that wants to discover what the library holds before
fetching detail.

## Request and response contracts

The five-route resource shape validates through a `FormRequest` in
[`src/Http/Requests/`](../../../src/Http/Requests) and returns a `JsonResource`
or `ResourceCollection` from
[`src/Http/Resources/`](../../../src/Http/Resources) — see
[Input Validation](/security/input-validation.md).

**The five non-resource routes do neither.** `library.index`, `books.addTags`,
`books.removeTag`, `books.addGenres`, and `books.removeGenre` return a plain
`response()->json([...])` rather than a resource.

None of the four book-pivot routes uses a form request, so their input is not
validated the way the resource routes' input is: a non-existent tag or genre id
surfaces as a database foreign-key error rather than a 422. Validate these
payloads in the host application. See
[Authorization Boundary](/security/authorization-boundary.md).

Controllers return `Illuminate\Http\JsonResponse` and use
`Symfony\Component\HttpFoundation\Response` constants for status codes rather
than integer literals.

## Machine-readable specification

Controllers, form requests, resources, models, and enums all carry `@OA\*`
annotations. The generated OpenAPI document is the authoritative contract — see
[OpenAPI Generation](/features/openapi-generation.md). Tags are namespaced
`Biblioteca/<Entity>` so the package's operations stay grouped when merged into
a host application's specification.

# Citations

- Verified 2026-09-04 against git HEAD — every route, route name, and path
  parameter enumerated from `routes/api.php`.
- Verified 2026-09-04 against git HEAD — the `genres` show, update, and destroy
  routes bind `{tag_id}` in `routes/api.php`.
- Verified 2026-09-04 against git HEAD — `LibraryController::index()` returns
  `series_ids` and `book_ids` via `Series::pluck()` and `Book::pluck()`.
- Verified 2026-09-04 against git HEAD — the four book-pivot methods in
  `BookController` take no `FormRequest`; `LibraryController::index()` returns
  `response()->json()`.
- Verified 2026-09-04 against git HEAD — `AuthorController` imports
  `Symfony\Component\HttpFoundation\Response as HttpCodes` and carries an
  `@OA\Tag(name="Biblioteca/Authors")` annotation.
