---
type: Security Control
title: Authorization Boundary
description: The package ships no authentication or authorization, and what the host application must add before exposing its routes.
resource: routes/api.php
tags: [security, authorization, laravel]
timestamp: 2026-09-04T00:00:00Z
---

# Authorization Boundary

**The package enforces no authentication and no authorization.** This is by
design, and it is the single most important thing to know before mounting its
routes.

## Two places the boundary is open

**1. The route file has no middleware.** Every route in
[`routes/api.php`](../../../routes/api.php) — including all `POST`, `PUT`, and
`DELETE` routes — is declared bare. The file's own header comment says so and
shows the intended pattern:

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('publishers', [PublisherController::class, 'store'])->name('publishers.store');
    Route::put('publishers/{publisher_id}', [PublisherController::class, 'update'])->name('publishers.update');
    Route::delete('publishers/{publisher_id}', [PublisherController::class, 'destroy'])->name('publishers.destroy');
});
```

**2. Every form request authorizes everything.** Each `FormRequest` in
[`src/Http/Requests/`](../../../src/Http/Requests) implements `authorize()` as
`return true;`. A form request is therefore a validation gate only, never an
access gate — see [Input Validation](/security/input-validation.md).

Validation coverage is also not uniform across the route file — see
[Input Validation](/security/input-validation.md).

## What the host application must do

Before exposing any of these routes:

1. **Do not load `routes/api.php` unmodified.** Copy it, or register the
   controllers under the host application's own route file.
2. **Wrap the write routes in authentication and authorization middleware.**
   `GET` routes may or may not need the same treatment, depending on whether the
   library content is public.
3. **Add rate limiting.** The package sets no throttle.
4. **Decide on ownership rules.** The models carry no owner column and no
   policy. If one tenant's books must not be visible to another, that scoping
   belongs in the host application.

## Why this matters more than it looks

The write routes are not equally consequential. Deletes cascade throughout the
schema, so removing one author removes that author's books and every chapter,
paragraph, sentence, note, figure, index entry, and pivot row beneath them — see
[Database Schema](/data/models/database-schema.md). Updating a chapter rebuilds
its paragraphs and sentences from scratch — see
[Chapter Text Parsing](/features/chapter-text-parsing.md).

Treat every route in the example file as privileged, not just the obviously
destructive ones.

# Citations

- Verified 2026-09-04 against git HEAD — `routes/api.php` declares no
  `Route::middleware(...)` call; the quoted example is its header comment.
- Verified 2026-09-04 against git HEAD — `AuthorRequest::authorize()` returns
  `true`; the same pattern holds across `src/Http/Requests/`.
- Verified 2026-09-04 against git HEAD — `ChapterService::parseChapterContents()`
  contains no `DB::transaction()` call.
- Verified 2026-09-04 against git HEAD — foreign keys in
  `database/migrations/2024_10_07_000000_create_bibliotecha_tables.php` use
  `onDelete('cascade')` except `b_books.publisher_id`.
