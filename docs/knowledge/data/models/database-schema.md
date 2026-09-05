---
type: Database Table
title: Database Schema
description: The b_-prefixed tables created by the package migration, their keys, unique constraints, and foreign-key cascade behaviour.
resource: database/migrations/2024_10_07_000000_create_bibliotecha_tables.php
tags: [data, schema, migrations]
timestamp: 2026-09-06T00:00:00Z
---

# Database Schema

One migration,
[`2024_10_07_000000_create_bibliotecha_tables.php`](../../../../database/migrations/2024_10_07_000000_create_bibliotecha_tables.php),
creates every table. It is loaded into the host application by the service
provider — see [System Overview](/architecture/system-overview.md).

## Naming

All eighteen tables carry the `b_` prefix. The models derive their table names
from
[`BibliotecaConstants::TABLE_PREFIX`](../../../../src/Constants/BibliotecaConstants.php);
the migration writes the prefixed names as literals and does not reference the
constant, so a change to the constant alone would desynchronise the two. The
tables are:
`b_authors`, `b_publishers`, `b_series`, `b_books`, `b_chapters`,
`b_paragraphs`, `b_sentences`, `b_bibliographies`, `b_figures`, `b_genres`,
`b_indices`, `b_notes`, `b_table_of_contents`, `b_tags`, `b_book_tags`,
`b_book_genres`, `b_series_books`, `b_annotations`.

## Column conventions

- **Primary keys** are `uuid` columns named after the entity — `book_id`,
  `chapter_id`, `toc_id` — not `id`. See
  [UUID Identifiers](/features/uuid-identifiers.md).
- **Timestamps** are always
  `$table->timestamp(Model::CREATED_AT)->useCurrent()` and
  `$table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()`.
- **Every table and every column carries a `comment()`.** This is a hard
  convention — see [Conventions](/style/conventions.md).

## Ordering constraints

The text hierarchy is ordered, and the ordering is enforced in the database
rather than in application code:

| Table          | Unique constraint                  |
| -------------- | ---------------------------------- |
| `b_books`      | `(title, author_id, publisher_id)` |
| `b_chapters`   | `(book_id, chapter_number)`        |
| `b_paragraphs` | `(chapter_id, paragraph_number)`   |
| `b_sentences`  | `(paragraph_id, sentence_number)`  |

Because of these constraints, re-parsing a chapter must replace the existing
child rows rather than append to them — see
[Chapter Text Parsing](/features/chapter-text-parsing.md).

## Cascade behaviour

Foreign keys use `onDelete('cascade')` throughout, with one exception:
`b_books.publisher_id` uses `onDelete('set null')`, because `publisher_id` is
nullable and a book outlives its publisher.

Cascading means deleting one author removes that author's books, and with them
every chapter, paragraph, sentence, note, figure, index entry, table-of-contents
entry, and pivot row beneath. Callers should treat an `Author` delete as a
large, irreversible operation.

`b_annotations` declares **no** foreign key. It cannot: `reference_id` is
polymorphic and may point at either `b_paragraphs` or `b_sentences`. Annotation
rows are therefore not cascaded away when their target is deleted, and an
application that deletes text should clean them up itself.

`reference_type` is a plain `string` column, so the database does not constrain
what it holds. Since 3.0.0 it stores a morph alias — `b_paragraphs` or
`b_sentences` — rather than a class name. `Annotation` constrains ordinary model
writes and its own read paths; writes that skip the attribute pipeline, and the
relationship-existence query family, are not covered. See
[Input Validation](/security/input-validation.md).

A second migration,
`2026_09_06_000000_alias_annotation_reference_types.php`, rewrites rows written
by earlier releases from the class name to the alias. It matches
case-insensitively, because PHP resolves class names that way, and matches the
`\`-prefixed form, which releases before 2.2.0 could store verbatim. Its
class⇄alias pairs and table name are **frozen literals, not references to
`Annotation::REFERENCE_TYPES`**: a migration is replayed by every fresh install
and must keep doing what it did when it was written, so a later change to that
constant cannot retroactively alter what a 2.x upgrade writes.

The target alias is read from the morph map, so a host that registers its own
alias for one of these models has that alias written rather than the package's —
otherwise the migrated rows would not match the relation the host's own
`getMorphClass()` constrains on. Where the map holds no alias for the class, the
rewrite is skipped rather than forced to the package alias: that state means a
host has displaced the package's entry, and 3.0.0 stores the class name there
too, so the untouched rows keep matching.

`down()` restores the class names, in canonical letter case, and is intended for
a downgrade to 2.x. It is less conservative than `up()`: it rewrites the
package's aliases whoever wrote them. Rows holding a value the package never
wrote are left untouched in both directions.

## Composite primary keys

`b_book_tags`, `b_book_genres`, and `b_series_books` declare
`$table->primary([...])` over two UUID columns rather than a surrogate key.
`b_series_books` adds an integer `number` column for the position of the book in
the series. See [Domain Model](/data/models/domain-model.md).

## Reversibility

`down()` drops all eighteen tables, but **not in reverse dependency order**. At
least three pairs are inverted: `b_sentences` is dropped before `b_notes`, which
references it; `b_publishers` before `b_books`; and `b_books` before
`b_bibliographies`. Rollback succeeds on engines that do not enforce foreign
keys during a drop — SQLite as configured in CI, and MySQL or MariaDB with
`FOREIGN_KEY_CHECKS` off — and can fail on an engine that does enforce them.
Verify a rollback on the target engine before relying on it.

# Citations

- Verified 2026-09-04 against git HEAD — table list, `primary()` declarations,
  `unique()` constraints, and `onDelete()` modes read from
  `database/migrations/2024_10_07_000000_create_bibliotecha_tables.php`.
- Verified 2026-09-04 against git HEAD — `b_books.publisher_id` is the only
  foreign key using `onDelete('set null')`; all others use `cascade`.
- Verified 2026-09-04 against git HEAD — the `b_annotations` `Schema::create`
  block declares `reference_id` and `reference_type` and no `foreign()` call.
- Verified 2026-09-04 against git HEAD — `BibliotecaConstants::TABLE_PREFIX` is
  `'b_'`, and the migration's `Schema::create` calls pass literal names
  (`'b_authors'`, `'b_books'`, …) rather than the constant.
- Verified 2026-09-04 against git HEAD — the `down()` drop order lists
  `b_sentences` before `b_notes`, `b_publishers` before `b_books`, and `b_books`
  before `b_bibliographies`.
