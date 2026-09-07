---
type: API Endpoint Group
title: REST Endpoints
description: The example API route file, the resource endpoints it declares, and the request and response contracts behind them.
resource: routes/api.php
tags: [api, rest, routes, openapi]
timestamp: 2026-09-05T12:00:00Z
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

**The five non-resource routes return no resource.** `library.index`,
`books.addTags`, `books.removeTag`, `books.addGenres`, and `books.removeGenre`
return a plain `response()->json([...])`. Two of them — `books.addTags` and
`books.addGenres` — do validate through a `FormRequest` as of 2.3.0.

Since 2.3.0 the two book-pivot routes that take a body do use a form request:
`books.addTags` takes `BookTagRequest` and `books.addGenres` takes
`BookGenreRequest`, each requiring an array whose every element is a UUID
present in the referenced table. A batch containing one unknown identifier
attaches none of it.

`books.removeTag` and `books.removeGenre` carry no body, so they take no request
object, but they resolve their path identifier with `findOrFail()` — an unknown
one is a 404 rather than the silent 200 it used to be.

Before 2.3.0 none of the four validated anything, and an unknown identifier
surfaced as a database foreign-key error rather than a 422. See
[Input Validation](/security/input-validation.md) and
[Authorization Boundary](/security/authorization-boundary.md).

Controllers return `Illuminate\Http\JsonResponse` and use
`Symfony\Component\HttpFoundation\Response` constants for status codes rather
than integer literals.

## Machine-readable specification

Controllers, form requests, resources, models, and enums all carry `#[OA\...]`
attributes. The generated OpenAPI document is the authoritative contract — see
[OpenAPI Generation](/features/openapi-generation.md). Tags are namespaced
`Biblioteca/<Entity>` so the package's operations stay grouped when merged into
a host application's specification.

# Citations

- Verified 2026-09-05 against git HEAD — `BookController::addTags()` and
  `addGenres()` accept `BookTagRequest` / `BookGenreRequest`; `removeTag()` and
  `removeGenre()` call `Tag::findOrFail()` / `Genre::findOrFail()`.

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
  `#[OA\Tag(name: 'Biblioteca/Authors')]` attribute.
