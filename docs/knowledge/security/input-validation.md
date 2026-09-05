---
type: Security Control
title: Input Validation
description: How Laravel form requests constrain incoming data, and the limits of that protection.
resource: src/Http/Requests
tags: [security, validation, laravel]
timestamp: 2026-09-04T00:00:00Z
---

# Input Validation

Validation is the one protection the package implements itself. Eleven
`FormRequest` classes live in
[`src/Http/Requests/`](../../../src/Http/Requests), one per entity that has an
HTTP surface. Laravel resolves them before the controller method runs, so on the
routes that use them an invalid payload does not reach a model.

**Coverage is not complete.** The four book-pivot routes validate nothing.
`books.addTags` and `books.addGenres` take a bare `Illuminate\Http\Request`;
`books.removeTag` and `books.removeGenre` take no request object at all, only
their two path parameters. See [REST Endpoints](/api/rest-endpoints.md).

## What the rules cover

Rules are declared as arrays, not pipe-delimited strings. Four kinds of
constraint appear:

- **Presence and type** — `['required', 'string', 'max:255']`.
- **Format** — `url` for image and cover URLs, `date` for publication dates,
  `integer` for ordering columns.
- **Referential existence** — foreign keys are checked with `exists:`, for
  example `['required', 'exists:' . Author::TABLE_NAME . ',author_id']` in
  [`BookRequest`](../../../src/Http/Requests/BookRequest.php). This stops a
  write that would otherwise fail at the database constraint.
- **Uniqueness** — [`AuthorRequest`](../../../src/Http/Requests/AuthorRequest.php)
  uses `Rule::unique()` scoped to `first_name`.

> **The update-safe half of that unique rule does not work.** `AuthorRequest`
> writes `->ignore($this->route('author')?->author_id, 'author_id')`, but the
> route parameter is `author_id`, not `author`, and `AuthorController::update()`
> takes a `string $author_id` and calls `findOrFail()` itself — there is no
> route-model binding named `author`. `$this->route('author')` is therefore
> `null`, and `ignore(null)` ignores nothing. Re-submitting an author's own name
> on `PUT authors/{author_id}` fails validation as a duplicate of itself. This
> is a live defect in the package, not a documentation simplification.

Table names come from the model `TABLE_NAME` constants rather than string
literals, so a rule cannot drift away from the schema.

## Mass-assignment protection

Each of the 15 entity models declares an explicit `$fillable` list, so a payload
key that passes validation but is not fillable is dropped at the model boundary.
`BookTag` and `BookGenre` declare none; they are protected instead by Eloquent's
default `$guarded = ['*']`. See [Domain Model](/data/models/domain-model.md).

## What this control does not do

`authorize()` returns `true` in all eleven form requests. **Validation is not
authorization.** A request can be perfectly well-formed and still come from
someone with no right to make it. See
[Authorization Boundary](/security/authorization-boundary.md).

`Annotation` is polymorphic, and polymorphic columns deserve application-level
handling regardless of what a package does: register
`Relation::enforceMorphMap()` and constrain `reference_type` to the types your
application actually accepts.

Nor does validation cover:

- **Content sanitization.** No free-text field is sanitized anywhere. That
  includes `content`, `summary`, `biography`, `title`, `subtitle`,
  `suggested_citation`, `caption`, `figure_label`, and `description` — every one
  is stored exactly as submitted. Escape on output; do not treat the list above
  as exhaustive, and do not assume any field is safe.
- **URL fields are weakly constrained.** `author_image_url`, `cover_image_url`,
  `chapter_image_url`, and `image_url` carry Laravel's `url` rule, which accepts
  roughly two hundred schemes — `data:`, `file:`, `blob:`, `view-source:` and
  `chrome-extension:` among them. A value that passes `url` is **not** safe to drop into an `src` or
  `href`. Constrain the scheme yourself.
- **Size limits on text bodies.** No `max:` rule bounds any `text` column —
  chapter, paragraph, sentence, and annotation `content`, plus `biography`,
  `summary`, `suggested_citation`, and `description`. Bound them in your own
  application, and remember that a large chapter also multiplies into paragraph
  and sentence rows. See
  [Chapter Text Parsing](/features/chapter-text-parsing.md).

# Citations

- Verified 2026-09-04 against git HEAD — all 11 files in `src/Http/Requests/`
  implement `authorize(): bool` returning `true`.
- Verified 2026-09-04 against git HEAD — `BookRequest::rules()` uses
  `'exists:' . Author::TABLE_NAME . ',author_id'`.
- Verified 2026-09-04 against git HEAD — `AuthorRequest::rules()` uses
  `Rule::unique(Author::TABLE_NAME)->where('first_name', ...)->ignore(...)`.
- Verified 2026-09-04 against git HEAD — `Book` declares `protected $fillable`;
  `BookTag` and `BookGenre` declare none.
- Verified 2026-09-04 against git HEAD — `routes/api.php` declares
  `authors/{author_id}` and `AuthorController::update()` takes
  `string $author_id`, so `$this->route('author')` in `AuthorRequest` is `null`.
- Verified 2026-09-04 against git HEAD — no `max:` rule appears on any `content`,
  `biography`, `summary`, `suggested_citation`, or `description` field in
  `src/Http/Requests/`.
