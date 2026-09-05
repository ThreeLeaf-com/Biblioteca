# Biblioteca User Guide

This guide is for the **application developer** who installs Biblioteca into a
Laravel application. It covers installation, the models, and the API routes.

For internal engineering detail, see the
[Technical Manual](../knowledge/index.md).

## Contents

1. [What Biblioteca gives you](#what-biblioteca-gives-you)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Working with the models](#working-with-the-models)
5. [Using the API routes](#using-the-api-routes)
6. [Before you go to production](#before-you-go-to-production)
7. [Troubleshooting](#troubleshooting)

## What Biblioteca gives you

Biblioteca is a Laravel package that models written material. It supplies
Eloquent models, database migrations, and factories for:

| Entity                | What it represents                           |
| --------------------- | -------------------------------------------- |
| **Author**            | The writer of one or more books              |
| **Publisher**         | The publisher of a book                      |
| **Series**            | An ordered collection of books by an author  |
| **Book**              | A single work                                |
| **Chapter**           | An ordered division of a book                |
| **Paragraph**         | An ordered division of a chapter             |
| **Sentence**          | An ordered division of a paragraph           |
| **Note**              | A footnote or endnote on a sentence          |
| **Annotation**        | Free-text comment on a paragraph or sentence |
| **Figure**            | An illustration in a chapter                 |
| **Bibliography**      | A reference entry for a book                 |
| **Index**             | A term and the page it appears on            |
| **Table of contents** | A chapter title and its page number          |
| **Tag**               | A free-form label on a book                  |
| **Genre**             | A category on a book                         |

It also supplies optional API controllers, form requests, JSON resources, and an
example route file.

It does **not** supply authentication, authorization, a user interface, or a
configuration file.

## Requirements

- PHP 8.2 or later
- Laravel 12 or Laravel 13
- A PDO-capable database

Version 2.1.0 and later support Laravel 12 and 13. Version 2.0.0 supports
Laravel 12 only. For Laravel 10, use version `^1.0`.

## Installation

**1. Require the package.**

```bash
composer require threeleaf/biblioteca
```

There is no publish step. The package registers itself through Laravel package
discovery, and its service provider publishes no assets and no configuration
file — it only loads the migrations.

**2. Run the migrations.**

```bash
php artisan migrate
```

This creates eighteen tables, all prefixed `b_`, so they will not collide with
your own. See the
[Database Schema](../knowledge/data/models/database-schema.md) concept for the
full list.

## Working with the models

The models are ordinary Eloquent models in the `ThreeLeaf\Biblioteca\Models`
namespace.

Note that four of them — `Bibliography`, `Index`, `Note`, and
`TableOfContents` — have no controller, no request, and no route. They are
reachable through Eloquent only.

### Create an author and a book

```php
use ThreeLeaf\Biblioteca\Models\Author;
use ThreeLeaf\Biblioteca\Models\Book;

$author = Author::create([
    'first_name' => 'Ada',
    'last_name'  => 'Example',
    'biography'  => 'Ada Example writes adventure fiction...',
]);

$book = Book::create([
    'title'          => 'The Great Adventure',
    'author_id'      => $author->author_id,
    'published_date' => now(),
    'summary'        => 'A thrilling tale of adventure...',
]);

echo $book->author->first_name;
```

Primary keys are UUIDs named after the entity — `author_id`, `book_id`,
`chapter_id` — not `id`. They are generated for you.

For `Author`, `Publisher`, `Series`, `Book`, `Chapter`, `Paragraph`, and
`Sentence` the id is **deterministic**: it is a UUID v5 hashed from the row's own
identifying fields, so the same content always yields the same id. That makes
re-imports idempotent, but it also means two authors with the same first and
last name collide on the primary key. The remaining models get their key from
Laravel's `HasUuids`, which is time-ordered rather than random — do not treat any
of these ids as unguessable. See
[UUID Identifiers](../knowledge/features/uuid-identifiers.md).

### Add a chapter, and let the text parse itself

You can create chapters, paragraphs, and sentences by hand:

```php
use ThreeLeaf\Biblioteca\Models\Chapter;
use ThreeLeaf\Biblioteca\Models\Paragraph;

$chapter = Chapter::create([
    'book_id'        => $book->book_id,
    'chapter_number' => 1,
    'title'          => 'Chapter 1: The Beginning',
]);

$paragraph = Paragraph::create([
    'chapter_id'       => $chapter->chapter_id,
    'paragraph_number' => 1,
    'content'          => 'This is the first paragraph of the chapter...',
]);
```

Or you can write the chapter's `content` through `ChapterService` and let the
package split it for you. Each line becomes a paragraph, and each sentence
becomes a `Sentence` row:

```php
use ThreeLeaf\Biblioteca\Services\ChapterService;

$chapterService = app(ChapterService::class);

$chapter = $chapterService->create([
    'book_id' => $book->book_id,
    'title'   => 'Chapter 1: The Beginning',
    'content' => "The lamp was still burning when she reached the landing. She set it down and listened.\nBelow, the front door closed.",
]);
```

If `chapter_number` is omitted, the service assigns the next one for that book.

> **Important:** parsing replaces the chapter's paragraphs and sentences every
> time the chapter is created or updated through the service. Any edit made
> directly to a `Paragraph` or `Sentence` row is discarded on the next chapter
> update. Treat the chapter's `content` as the authority.
>
> The replace is atomic as of 2.2.1: the old rows are deleted before the new
> ones are written, but both happen in one transaction, so a failure in between
> rolls the delete back and your existing rows are never lost. On 2.2.0 and
> earlier it was not, and such a failure left the rows deleted — wrap your own
> `ChapterService` calls in `DB::transaction()` on those releases. See
> [Chapter Text Parsing](../knowledge/features/chapter-text-parsing.md).

Writing a `Paragraph` directly does **not** re-parse its sentences — only a
write to the parent chapter does that.

### Query relationships

```php
$booksByAuthor = Author::find($author->author_id)->books;

foreach ($booksByAuthor as $book) {
    echo $book->title;
}

$chapters   = $book->chapters;
$tags       = $book->tags;
$genres     = $book->genres;
$publisher  = $book->publisher;
```

### Deletes cascade

Deleting an author deletes that author's books, and with them every chapter,
paragraph, sentence, note, figure, index entry, and table-of-contents entry
beneath. Deleting a publisher does **not** delete its books; their
`publisher_id` is set to `null` instead.

Annotations are the exception to the cascade: they carry no foreign key, so
deleting a paragraph or a sentence leaves its annotations behind. Clean them up
yourself if that matters to you.

### Annotations attach only to paragraphs and sentences

An annotation attaches to either a paragraph or a sentence, and `reference_type`
says which:

```php
$annotation = Annotation::create([
    'reference_id' => $paragraph->paragraph_id,
    'reference_type' => Paragraph::class,
    'content' => 'The date here is disputed.',
]);
```

Or let the relation set it for you, which is less to get wrong:

```php
$annotation = $paragraph->annotations()->create([
    'content' => 'The date here is disputed.',
]);
```

The value has to denote `Paragraph` or `Sentence`. Their class names work in any
letter case — a mis-cased name is stored in its canonical form so the parent's
`annotations()` still finds it — as does a subclass of either, and so does a
morph alias if you have registered one, which is kept exactly as you wrote it. Anything else throws `InvalidReferenceTypeException`, and the API
returns `422` for the same values. The API also rejects a `reference_id` that is
not a real row in the matching table.

The check runs when the reference is *read* as well as written, so a row holding
something impermissible raises rather than resolving.

### Upgrading from 2.1.0 or earlier

Earlier releases did not constrain `reference_type`, so the annotation endpoints
accepted any value there. If those endpoints were reachable, audit the column
before you upgrade:

```sql
SELECT reference_type, COUNT(*) AS total
  FROM b_annotations
 GROUP BY reference_type;
```

Every row should name `Paragraph` or `Sentence` — or an alias or subclass you
recognise. Anything else was not written by this package.

Nothing is cleaned up for you. That is deliberate: an automatic sweep cannot
distinguish a hostile row from a legitimate one whose morph map simply is not
registered in the process running migrations, and clearing the second would
destroy real data. Decide what those rows are and remove them yourself.

Until you do, be aware of two gaps the guards cannot close. Writes that skip
Eloquent's attribute pipeline — `Annotation::insert()`, `upsert()`,
`query()->update()`, and raw SQL — are not checked. And relationship-existence
queries (`has()`, `doesntHave()`, `whereHasMorph()`) read the column directly, so
they will still instantiate whatever an impermissible row names. Both need a row
this package will not write; neither is reachable through its API.

## Using the API routes

The package ships an example route file at
`vendor/threeleaf/biblioteca/routes/api.php` with full CRUD routes for eleven
entities plus an aggregate `GET library` endpoint. See
[REST Endpoints](../knowledge/api/rest-endpoints.md) for the complete list.

### Option A — write your own routes

Point your own routes at the package's controllers, or at your own:

```php
use ThreeLeaf\Biblioteca\Http\Controllers\Api\AuthorController;

Route::apiResource('authors', AuthorController::class);
```

### Option B — include the example file

```php
require base_path('vendor/threeleaf/biblioteca/routes/api.php');
```

**Read the warning below before doing this.**

## Before you go to production

**The package's routes have no authentication and no authorization.** Every
route in the example file — including every `POST`, `PUT`, and `DELETE` — is
declared without middleware, and every form request's `authorize()` method
returns `true`.

Combined with cascading deletes, an exposed `DELETE authors/{author_id}` will
remove an author's entire library.

Validation coverage is also not uniform: not every route in the example file
routes its input through a form request, so do not assume a payload reaching a
controller has been checked.

Before exposing any of these routes:

1. Wrap the write routes in your own auth middleware:

    ```php
    Route::middleware(['auth', 'role:admin'])->group(function () {
        require base_path('vendor/threeleaf/biblioteca/routes/api.php');
    });
    ```

2. Decide whether read routes should be public.
3. Add rate limiting — the package sets none.
4. Add tenant or ownership scoping if your data is not shared. The models carry
   no owner column and no policy.
5. **Escape every free-text field when rendering.** Nothing is sanitized:
   `content`, `summary`, `biography`, `title`, `subtitle`, `suggested_citation`,
   `caption`, `figure_label`, and `description` are all stored exactly as
   submitted. Do not treat that list as exhaustive.
6. **Do not trust the `*_image_url` fields as safe URLs.** Laravel's `url` rule
   accepts any of 312 schemes in `scheme://host` form, `file://` and
   `chrome-extension://` among them. Restrict the scheme to `http` and `https`
   before putting one in an `src` or `href`.
7. **Bound your text sizes.** No `max:` rule applies to any text column, so
   chapters, paragraphs, sentences, and annotations are unbounded unless your
   application limits them.

See [Authorization Boundary](../knowledge/security/authorization-boundary.md).

## Troubleshooting

**Migrations do not run.** The service provider loads them in `boot()` via
package discovery. If you have disabled discovery, register
`ThreeLeaf\Biblioteca\Providers\BibliotecaServiceProvider` yourself.

**A chapter's paragraphs disappeared.** They were replaced by a re-parse. See
the warning under [Add a chapter](#add-a-chapter-and-let-the-text-parse-itself).

**Two chapters got the same number.** `chapter_number` is assigned by reading
the current maximum, with no lock. Under concurrent creates the
`(book_id, chapter_number)` unique constraint rejects the loser. Retry, or pass
`chapter_number` explicitly.

**Sentences split in the wrong place.** The sentence splitter looks for terminal
punctuation followed by whitespace and a capital letter, so `Dr. Smith` splits
and non-Latin scripts do not split at all. Create `Sentence` rows directly if
you need different behaviour.

**Updating an author fails as a duplicate of itself.** `AuthorRequest`'s unique
rule tries to exempt the row being edited, but reads a route parameter named
`author` when the route declares `author_id`, so the exemption never applies.
This is a defect in the package. Until it is fixed, change the name on update or
write the author through Eloquent rather than the API. See
[Input Validation](../knowledge/security/input-validation.md).

**A `genres` route rejects your parameter name.** The `genres` show, update, and
destroy routes bind `{tag_id}` rather than `{genre_id}`. The value is still the
genre id.

**OpenAPI docs are stale.** They regenerate on `composer install`, not on
`composer update` — the generator is wired to Composer's `post-install-cmd`
hook only. See
[OpenAPI Generation](../knowledge/features/openapi-generation.md).
