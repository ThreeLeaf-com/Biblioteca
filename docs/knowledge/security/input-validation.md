---
type: Security Control
title: Input Validation
description: How Laravel form requests constrain incoming data, and the limits of that protection.
resource: src/Http/Requests
tags: [security, validation, laravel]
timestamp: 2026-09-05T00:00:00Z
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

## Polymorphic reference constraint

`Annotation` is polymorphic: `reference_type` names the class Eloquent resolves
when anything reads `$annotation->reference`. An unconstrained value there is a
class lookup driven by stored data, so the column is constrained in two layers.

`Annotation::REFERENCE_TYPES` is the allow-list — `Paragraph` and `Sentence`,
and nothing else.

**At the HTTP boundary**, `AnnotationRequest` restricts `reference_type` with
`Rule::in(Annotation::REFERENCE_TYPES)` and validates `reference_id` with
`Rule::exists()` against the table that type names, so an annotation cannot
point at a row that does not exist. `prepareForValidation()` strips a leading
backslash, since `\Foo\Bar` and `Foo\Bar` name the same class.

**At the model boundary**, the same allow-list is enforced regardless of how the
write arrives:

| Path                                  | Guard                                             |
| ------------------------------------- | ------------------------------------------------- |
| `Annotation::create()`, `fill()`, `$a->reference_type = …` | `setReferenceTypeAttribute()` |
| `$annotation->reference` (lazy)       | `Annotation::getActualClassNameForMorph()`        |
| `Annotation::with('reference')` (eager), `->load('reference')` | `ReferenceMorphTo::createModelByType()` |

All four raise `InvalidReferenceTypeException`.

The HTTP layer alone would not be enough. The package's own user guide documents
`Annotation::create()` as ordinary usage, and that path never reaches a form
request. Neither would a write guard alone: a row stored by a release that did
not constrain the column is still resolved on read, and that is exactly the
population a patch for this has to protect. Guarding resolution means such a row
raises an exception instead of instantiating the class it names.

The lazy and eager paths need separate guards because they resolve the class
differently. `HasRelationships::morphInstanceTo()` calls
`static::getActualClassNameForMorph()`, which late static binding routes to
`Annotation`. `MorphTo::createModelByType()` calls
`Model::getActualClassNameForMorph()` — statically, on the base class — so an
override on `Annotation` never runs there. `ReferenceMorphTo` covers it.

Eloquent does not run mutators when hydrating from the database, so reading an
`Annotation` row does not itself throw. Only resolving its reference does.

### What this does not constrain

The database does not enforce the allow-list; `b_annotations.reference_type` is
a plain `string` column with no foreign key, and cannot have one while it is
polymorphic. A direct `INSERT`, or an `UPDATE` issued through the query builder
rather than the model, bypasses the mutator. The resolution guard is what makes
that safe to leave: such a row can be written, but it cannot be resolved.

## What this control does not do

`authorize()` returns `true` in all eleven form requests. **Validation is not
authorization.** A request can be perfectly well-formed and still come from
someone with no right to make it. See
[Authorization Boundary](/security/authorization-boundary.md).

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

- Verified 2026-09-05 against git HEAD — `Annotation::REFERENCE_TYPES` lists
  `Paragraph::class` and `Sentence::class`; `AnnotationRequest::rules()` applies
  `Rule::in(Annotation::REFERENCE_TYPES)`.
- Verified 2026-09-05 by execution — `Annotation::create()`,
  `$annotation->reference`, `Annotation::with('reference')` and
  `->load('reference')` each raise `InvalidReferenceTypeException` for an
  unpermitted type, including for a row inserted through the query builder.
- Verified 2026-09-05 against Laravel 12/13 —
  `HasRelationships::morphInstanceTo()` calls
  `static::getActualClassNameForMorph()` while `MorphTo::createModelByType()`
  calls `Model::getActualClassNameForMorph()`.
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
