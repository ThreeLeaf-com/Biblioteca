---
type: Feature
title: Chapter Text Parsing
description: How chapter content is split into paragraph rows and sentence rows, why the operation is destructive, and how it is made atomic.
resource: src/Services/ChapterService.php
tags: [feature, parsing, chapters, paragraphs, sentences]
timestamp: 2026-09-05T00:00:00Z
---

# Chapter Text Parsing

Writing a chapter's `content` is enough to populate the whole text hierarchy
beneath it. `ChapterService` and `ParagraphService` derive the paragraph and
sentence rows automatically.

## When it runs

[`ChapterService`](../../../src/Services/ChapterService.php) calls
`parseChapterContents()` at the end of `create()`, `update()`, and
`updateOrCreate()`. There is no way to write a chapter through the service and
skip parsing.

## The two passes

**Chapter → paragraphs.** `normalizeContent()` trims the string and collapses
`\r\n`, `\r`, and runs of `\n` into a single `\n`. `parseToParagraphs()` then
splits on `/\n+/` with `PREG_SPLIT_NO_EMPTY`. One line becomes one paragraph.
Each paragraph is created with `paragraph_number` assigned sequentially from 1.

**Paragraph → sentences.**
[`ParagraphService::parseParagraphContents()`](../../../src/Services/ParagraphService.php)
collapses all whitespace to single spaces, then splits on the lookaround pattern
`/(?<=[.!?])\s+(?=[A-Z])/` — a sentence boundary is terminal punctuation
followed by whitespace followed by a capital letter. `sentence_number` is
assigned sequentially from 1.

Both passes have an inverse: `combineIntoChapter()` joins paragraphs with `\n`,
and `combineIntoParagraph()` joins sentences with a space.

## Parsing is destructive

Both methods **delete all existing children before re-creating them** —
`deleteAllParagraphs()` and `deleteAllSentences()` run first. This is required,
not incidental: `b_paragraphs` is unique on `(chapter_id, paragraph_number)` and
`b_sentences` on `(paragraph_id, sentence_number)`, so appending would collide.
See [Database Schema](/data/models/database-schema.md).

The consequence is that **any edit made directly to a paragraph or sentence row
is lost the next time its chapter is updated through the service.** Treat
chapter `content` as the authority and the derived rows as a cache.

**The delete-then-rebuild sequence is transactional** as of 2.2.1. Both
`parseChapterContents()` and `parseParagraphContents()` wrap their work in
`DB::transaction()`, so a rebuild that fails part-way — a constraint violation, a
lost connection, a PHP error — rolls the delete back and leaves the existing
paragraphs and sentences in place. Chapter parsing calls paragraph parsing, and
Laravel nests the inner transaction as a savepoint, so the sentences written
during a chapter rebuild cannot commit independently of it.

Before 2.2.1 neither did, and such a failure left the chapter with its
paragraphs and sentences gone and not restored. The workaround on those releases
is to wrap your own `ChapterService` calls in `DB::transaction()`.

One caller-visible consequence: a deadlock raised inside the nested transaction
surfaces as `Illuminate\Database\DeadlockException`, which is **not** a
`QueryException`. Host code that catches `QueryException` around chapter parsing
no longer catches that case. The data stays consistent either way — the
transaction rolls back — but the exception type changed.

Annotations are a related hazard. `b_annotations` has no foreign key, so
annotations pointing at deleted paragraph or sentence rows are left orphaned
rather than cascaded away. See [Domain Model](/data/models/domain-model.md).

## Chapter numbering

`assignChapterNumber()` fills in `chapter_number` when the caller omits it, when
it is not numeric, or when it is less than 1. It reads the highest existing
`chapter_number` for the book and adds one, defaulting to 1 for the first
chapter. This is a read-then-write with no lock, so two concurrent chapter
creations for the same book can pick the same number; the
`(book_id, chapter_number)` unique constraint turns that race into a failed
insert rather than a duplicate.

## Known limits of the sentence pattern

The pattern is deliberately simple, and it splits where a human would not:

- Abbreviations — `Dr. Smith`, `Mr. Jones`, `U.S. Army`.
- A sentence that continues with a lower-case word after a quotation mark.
- Non-Latin scripts, where `[A-Z]` never matches, so the whole paragraph stays
  one sentence.

# Citations

- Verified 2026-09-04 against git HEAD — `ChapterService::create()`,
  `update()`, and `updateOrCreate()` each end with `parseChapterContents()`.
- Verified 2026-09-04 against git HEAD — `parseChapterContents()` calls
  `chapterRepository->deleteAllParagraphs($chapter)` before creating any
  paragraph; `parseParagraphContents()` calls
  `paragraphRepository->deleteAllSentences($paragraph)` first.
- Verified 2026-09-04 against git HEAD — `normalizeContent()` uses
  `preg_replace("/\r\n|\r|\n+/", "\n", trim($content))`; `parseToParagraphs()`
  splits on `/\n+/`; `parseToSentences()` splits on `/(?<=[.!?])\s+(?=[A-Z])/`.
- Verified 2026-09-05 against git HEAD — `ChapterService::parseChapterContents()`
  and `ParagraphService::parseParagraphContents()` each wrap their body in
  `DB::transaction()`.
- Verified 2026-09-05 by execution — with the transactions removed, forcing a
  failure between the delete and the rebuild loses the paragraphs and their
  sentences; with them in place, both are restored.
- Verified 2026-09-04 against git HEAD — `ParagraphController` does not call
  `ParagraphService`, so writing a paragraph directly does not re-parse its
  sentences.
- Verified 2026-09-04 against git HEAD — `assignChapterNumber()` queries
  `Chapter::where('book_id', ...)->orderBy('chapter_number', 'desc')->first()`
  and defaults to 1.
