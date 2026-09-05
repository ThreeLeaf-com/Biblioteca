---
type: Style Guide
title: Glossary
description: Shared terms used across the Biblioteca documentation bundle.
resource: src/Models
tags: [style, glossary, shared-language]
timestamp: 2026-09-04T00:00:00Z
---

# Glossary

Terms used consistently across this bundle. Where a term names a model, the
model is the definition of record.

| Term                  | Meaning                                                                                                                   |
| --------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| **Annotation**        | A comment attached polymorphically to a paragraph or a sentence. Distinct from a _note_.                                  |
| **Author**            | The writer of one or more books. The root of the containment chain.                                                       |
| **Bibliography**      | A reference entry belonging to a book.                                                                                    |
| **Book**              | A single work. Belongs to an author and, optionally, a publisher; may belong to series.                                   |
| **Chapter**           | An ordered division of a book, unique on `(book_id, chapter_number)`.                                                     |
| **Containment chain** | Author → Book → Chapter → Paragraph → Sentence → Note. Deletes cascade down it.                                           |
| **Figure**            | An illustration attached to a chapter.                                                                                    |
| **Genre**             | A category attached to books through the `b_book_genres` pivot.                                                           |
| **Host application**  | The Laravel application that installs this package. It owns routing, auth, and deployment.                                |
| **Index**             | An index entry: a term plus the page number where it appears in a book. Not the OKF `index.md`.                           |
| **Note**              | Content attached to a _sentence_, typed `FOOTNOTE`, `ENDNOTE`, or `BOTH`, in `PAGE`, `CHAPTER`, or `BOOK` context.        |
| **OKF**               | Open Knowledge Format v0.1 — the schema this bundle conforms to.                                                          |
| **Paragraph**         | An ordered division of a chapter, unique on `(chapter_id, paragraph_number)`. Usually derived by parsing chapter content. |
| **Parsing**           | Deriving paragraph rows from chapter content, and sentence rows from paragraph content. Destructive and re-runnable.      |
| **Publisher**         | The publisher of a book. Optional; a book survives its publisher's deletion.                                              |
| **Repository**        | A thin Eloquent wrapper. Exists only for `Chapter` and `Paragraph`.                                                       |
| **Sentence**          | An ordered division of a paragraph, unique on `(paragraph_id, sentence_number)`.                                          |
| **Series**            | An ordered collection of books by an author, ordered by `b_series_books.number`.                                          |
| **Service**           | A class holding behaviour beyond a plain write. Exists only for `Chapter`, `Paragraph`, and `Series`.                     |
| **Table of contents** | A `(book_id, chapter_id, title, page_number)` entry. Model `TableOfContents`, table `b_table_of_contents`, key `toc_id`.  |
| **Tag**               | A free-form label attached to books through the `b_book_tags` pivot.                                                      |

## Terms that are easy to confuse

- **Note vs Annotation.** A _note_ is a typed footnote or endnote and attaches
  to a sentence only. An _annotation_ is untyped free text and attaches
  polymorphically to a paragraph _or_ a sentence.
- **Index (model) vs `index.md` (OKF).** The `Index` model is a book index
  entry. `index.md` is the OKF bundle's reserved navigation file.
- **Series (model) vs `series` (route).** `series` is both the singular and the
  plural route segment — see [REST Endpoints](/api/rest-endpoints.md).

See [Domain Model](/data/models/domain-model.md) for how these connect.

# Citations

- Verified 2026-09-04 against git HEAD — entity list and relationships from
  `src/Models/*.php`.
- Verified 2026-09-04 against git HEAD — `NoteType` cases are `FOOTNOTE`,
  `ENDNOTE`, `BOTH`; `Context` cases are `PAGE`, `CHAPTER`, `BOOK`.
- Verified 2026-09-04 against git HEAD — `b_table_of_contents` uses primary key
  `toc_id` and columns `book_id`, `title`, `chapter_id`, `page_number`.
