---
type: Database Table
title: Domain Model
description: The Biblioteca entities and the Eloquent relationships that connect them.
resource: src/Models
tags: [data, eloquent, relationships]
timestamp: 2026-09-06T00:00:00Z
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

`reference_type` holds a **morph alias**, not a class name. As of 3.0.0 the
package registers a morph map keyed by the prefixed table names, so the column
stores `b_paragraphs` or `b_sentences`, and the persisted discriminator no
longer names a PHP class.

`Annotation::REFERENCE_TYPES` declares that map — `alias => class` — and is the
only set of models `reference_type` may denote. A value is accepted as an alias,
as a class name in any letter case, or as a subclass, and is stored as the
model's `getMorphClass()`. A value denoting anything else raises
`InvalidReferenceTypeException` on write and on resolution alike. See
[Input Validation](/security/input-validation.md).

The single constant is both the allow-list and the morph map, registered by
`BibliotecaServiceProvider::boot()` with `Relation::morphMap()`. `morphMap()`
rather than `Relation::enforceMorphMap()`, because the latter also sets the
process-global `requireMorphMap()` flag, which would make every unmapped morph
in the *host* application throw.

A host application that registers its own alias for `Paragraph` or `Sentence`
**from a service provider's `boot()`** takes precedence: `Relation::morphMap()`
merges as `$map + static::$morphMap`, which prepends, and `getMorphClass()` takes
the first match — so the last registration wins, and package providers boot
first. A host registering from `register()` or `bootstrap/app.php` runs before
this package and is overridden by it.

`Annotation` therefore stores `Relation::getMorphAlias()` of the resolved class
rather than reading `REFERENCE_TYPES` directly, so the stored value always
matches what `MorphMany` constrains on. Where no map is registered at all that
yields the class name, which is also what `MorphMany` constrains on in that
process — the two agree, which matters more than the column keeping its 3.0.0
shape.

The corollary is that a host claiming `b_paragraphs` or `b_sentences` for one of
its own models silently repoints the alias, and Laravel reports no conflict.

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

- Verified 2026-09-06 against git HEAD — `Annotation::REFERENCE_TYPES` maps
  `Paragraph::TABLE_NAME => Paragraph::class` and
  `Sentence::TABLE_NAME => Sentence::class`, and `BibliotecaServiceProvider`
  passes it to `Relation::morphMap()`.
- Verified 2026-09-06 by execution — `(new Paragraph())->getMorphClass()` returns
  `b_paragraphs` once the provider has booted, and a host alias registered
  afterwards takes precedence over it.

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
