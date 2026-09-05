# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.0] - 2026-09-05

### Fixed

- **The book tag and genre endpoints validate their input.** `addTags()` and
  `addGenres()` took a bare `Illuminate\Http\Request` and passed `tag_ids` /
  `genre_ids` straight to `syncWithoutDetaching()` with no `array`, `uuid` or
  `exists:` rule, and `removeTag()` / `removeGenre()` took no request object at
  all. On the add routes, malformed or unknown identifiers reached the database
  and surfaced as foreign-key errors (HTTP 500) rather than a 422; on the remove
  routes an unknown identifier was a silent no-op returning 200. They now use
  `BookTagRequest` and `BookGenreRequest`, and the remove routes resolve their
  path identifier with `findOrFail()`, so an unknown one is a 404.

  Three shapes that previously returned `200` now return `422`: an absent
  `tag_ids` / `genre_ids`, an empty array, and `null`. Each attached nothing
  before, so the outcome is unchanged — only the status. A batch containing one
  unknown identifier now attaches none of it rather than the valid half.
  Advisory:
  [GHSA-8ph5-c5p6-vhf9](https://github.com/ThreeLeaf-com/Biblioteca/security/advisories/GHSA-8ph5-c5p6-vhf9).

- **Chapter and paragraph re-parse is now atomic.**
  `ChapterService::parseChapterContents()` and
  `ParagraphService::parseParagraphContents()` delete every child row before
  writing replacements, because the child tables are unique on their position
  columns. That sequence was not wrapped in a transaction, so a failure
  part-way through left the paragraphs, and their sentences, deleted and not
  restored. Both now run in `DB::transaction()`.

  Chapter parsing calls paragraph parsing, and Laravel nests the inner
  transaction as a savepoint, so sentences written during a chapter rebuild
  cannot commit independently of it. One caller-visible consequence: a deadlock
  raised inside the nested transaction now surfaces as
  `Illuminate\Database\DeadlockException`, which is not a `QueryException`, so
  host code catching `QueryException` around chapter parsing no longer catches
  that case. Advisory:
  [GHSA-f6xp-r5g7-8wq7](https://github.com/ThreeLeaf-com/Biblioteca/security/advisories/GHSA-f6xp-r5g7-8wq7).

## [2.2.0] - 2026-09-05

### Security

- `Annotation.reference_type` is constrained to models the package permits —
  `Paragraph` and `Sentence`. The column names the class Eloquent resolves when
  the polymorphic reference is read, and it was previously validated only as a
  string, so a caller could store an arbitrary class name. It is now checked at
  the API boundary and on every ordinary model write, and again when the
  reference is resolved. An impermissible value raises
  `ThreeLeaf\Biblioteca\Exceptions\InvalidReferenceTypeException`.

### Changed

- **Breaking for some callers, deliberately.** Two request shapes that used to
  return `201` now return `422`:
  - a `reference_type` that does not denote `Paragraph` or `Sentence`;
  - a `reference_id` that is not an existing row in the table that type names.
    `reference_id` was previously validated as a UUID only, so dangling
    references were accepted.

  Both were part of the vulnerability rather than a supported contract. This
  ships as a minor release so that installations tracking `^2.0` receive the fix
  from a routine `composer update`.

- A `reference_type` submitted in a different letter case is now stored in its
  canonical class form, so the parent's `annotations()` relation still matches
  it. A morph alias registered by the host application is stored exactly as
  given.

### Notes for upgraders

If your annotation endpoints were reachable on an earlier release, audit the
column before upgrading — see *Upgrading from 2.1.0 or earlier* in
[the user guide](docs/user-guide/README.md). Nothing is cleaned up
automatically, because an automatic sweep cannot distinguish a hostile row from
a legitimate one whose morph map is not registered in the process running
migrations.

Two paths remain outside the guards and are documented in
[Input Validation](docs/knowledge/security/input-validation.md): writes that
skip Eloquent's attribute pipeline, and the relationship-existence query family,
which reads the column directly. Both require a row this package will not write,
and neither is reachable through its API.
