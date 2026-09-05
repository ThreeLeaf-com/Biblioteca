---
type: Database Table
title: Domain Model
description: The Biblioteca entities and the Eloquent relationships that connect them.
resource: src/Models
tags: [data, eloquent, relationships]
timestamp: 2026-09-04T00:00:00Z
---

# Domain Model

Eighteen models live in [`src/Models/`](../../../../src/Models). They form one
containment chain plus a set of satellites that attach to it.

## Containment chain

```
Author ──< Book ──< Chapter ──< Paragraph ──< Sentence ──< Note
```

Each level is a `HasMany` downward and a `BelongsTo` upward — with one gap:
`Sentence` declares no `notes()` relation, so the `Sentence → Note` edge exists
only upward, through `Note::sentence()`. Load a sentence's notes with an
explicit query rather than `$sentence->notes`.

## Satellites

| Model             | Attaches to             | Relationship            |
| ----------------- | ----------------------- | ----------------------- |
| `Publisher`       | `Book`                  | `HasMany` / `BelongsTo` |
| `Bibliography`    | `Book`                  | `BelongsTo`             |
| `Index`           | `Book`                  | `BelongsTo`             |
| `TableOfContents` | `Book` and `Chapter`    | `BelongsTo` (both)      |
| `Figure`          | `Chapter`               | `BelongsTo`             |
| `Annotation`      | `Paragraph`, `Sentence` | `MorphTo`               |

## Many-to-many

Three relationships are many-to-many, each with its own pivot table:

- `Book` ↔ `Tag` through `b_book_tags` (model `BookTag`)
- `Book` ↔ `Genre` through `b_book_genres` (model `BookGenre`)
- `Series` ↔ `Book` through `b_series_books` (model `SeriesBook`)

`SeriesBook` is not a bare pivot: it carries a `number` column that fixes the
position of a book within its series. `Series` also has its own
`BelongsTo Author`.

All three pivot models key on a pair of columns, but they get that behaviour
two different ways. `SeriesBook` extends `Model` and uses the
[`HasCompositeKey`](../../../../src/Traits/HasCompositeKey.php) trait: the trait
reads a `$primaryKeys` array — `['series_id', 'book_id']` — and sets every part
of the key when building a save or update query, because plain Eloquent supports
one key column only. `BookTag` and `BookGenre` extend Laravel's `Pivot` base
class, which already handles a two-column key, so they use neither the trait nor
`$primaryKeys`.

## Polymorphic annotations

`Annotation` uses `reference_id` plus `reference_type` and exposes a `MorphTo`
`reference()`. `Paragraph` and `Sentence` each expose a `MorphMany`
`annotations()`. This lets one annotation table serve both text levels without a
second table or a nullable-column-per-target design.

## Enumerations

`Note` carries two backed string enums from
[`src/Enums/`](../../../../src/Enums):

- [`NoteType`](../../../../src/Enums/NoteType.php) — `FOOTNOTE`, `ENDNOTE`, `BOTH`
- [`Context`](../../../../src/Enums/Context.php) — `PAGE`, `CHAPTER`, `BOOK`

The migration derives the database `enum` column values from
`NoteType::cases()` and `Context::cases()`, so the PHP enum is the single source
of truth for the allowed values.

## Model conventions

Each of the 15 **entity** models declares its own table through a `TABLE_NAME`
constant, an explicit `$primaryKey` (`book_id`, `chapter_id`, and so on — never
`id`), a `$fillable` list, a full PHPDoc `@property` block, and an
`@OA\Schema` annotation used by
[OpenAPI generation](/features/openapi-generation.md). See
[Conventions](/style/conventions.md).

`SeriesBook` follows the same conventions, because it carries a real `number`
column and is part of the API surface. `BookTag` and `BookGenre` do not: each is
a 13-line `Pivot` subclass holding only a `TABLE_NAME` constant — no `$fillable`,
no `@property` block, and no `@OA\Schema`.

For the physical tables and their cascade rules, see
[Database Schema](/data/models/database-schema.md).

# Citations

- Verified 2026-09-04 against git HEAD — relationship methods enumerated from
  `src/Models/*.php`; `Annotation::reference()` is `MorphTo`, and
  `Paragraph::annotations()` and `Sentence::annotations()` are `MorphMany`.
- Verified 2026-09-04 against git HEAD — `SeriesBook` declares
  `protected array $primaryKeys = ['series_id', 'book_id']` and
  `protected $table = self::TABLE_NAME`; `BookTag` and `BookGenre` extend
  `Pivot` and declare only `TABLE_NAME`.
- Verified 2026-09-04 against git HEAD — `src/Models/Sentence.php` declares
  `paragraph(): BelongsTo` and `annotations(): MorphMany` and no `notes()`
  relation.
- Verified 2026-09-04 against git HEAD — the `b_notes` migration builds its
  `note_type` and `context` enum columns from `NoteType::cases()` and
  `Context::cases()`.
- Verified 2026-09-04 against git HEAD — `Book` declares
  `protected $primaryKey = 'book_id'`.
