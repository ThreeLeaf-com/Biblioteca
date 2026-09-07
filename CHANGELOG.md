# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.1] - 2026-09-07

### Fixed

- **The OpenAPI document is generated again.** swagger-php 6.7.1 reads docblock
  `@OA\*` annotations only when `doctrine/annotations` is installed, and it is
  not a dependency of this package. Every annotation in the package — 653 lines
  across 54 files — was inert, and `composer install` wrote
  `{"openapi":"3.0.0"}` with no paths and no schemas. A consumer generating a
  client from the specification got an empty document.

  The annotations are now PHP attributes (`#[OA\...]`), which swagger-php reads
  with no extra dependency and which is the direction the library has taken —
  its roadmap deprecates annotations in v7 and removes them in v8.
  `doctrine/annotations` was not adopted: Composer reports it as abandoned. The
  migration is behaviour-preserving; the generated document is identical to the
  one the annotations produced, at 28 paths and 42 schemas.

  This is a source-level change only. No model, route, request, or response
  behaviour changes, and nothing a consumer calls has a different shape. Host
  code that scans this package's source for `@OA\*` docblocks — there is no
  documented reason to — will no longer find any.
  ([#20](https://github.com/ThreeLeaf-com/Biblioteca/issues/20))

- **An empty OpenAPI document fails the build.** `util/generate-open-api.php`
  caught its own exceptions, printed them, and exited `0`, so the build reported
  success on a document with nothing in it. It now exits `1` on a `Throwable`
  and on a document with zero paths or zero schemas, and reports the schema
  count alongside the path count.
  `tests/Unit/OpenApi/OpenApiDocumentTest.php` makes the same assertion in CI,
  which runs `composer update` and so never invokes the generator.
  ([#20](https://github.com/ThreeLeaf-com/Biblioteca/issues/20))

## [3.0.0] - 2026-09-06

### Changed

- **BREAKING: `Annotation.reference_type` stores a morph alias, not a class
  name.** The package now registers a morph map for the two models an
  annotation can reference, so the column holds `b_paragraphs` or
  `b_sentences` where it previously held
  `ThreeLeaf\Biblioteca\Models\Paragraph` or
  `ThreeLeaf\Biblioteca\Models\Sentence`. The persisted discriminator no longer
  names a PHP class, and the API no longer publishes the package's internal
  namespace.

  **This breaks silently.** Host code that compares `reference_type` to a class
  name, or queries on one, still runs and simply stops matching. Audit for
  `where('reference_type', ...)`, `$annotation->reference_type === Foo::class`,
  and any client asserting on the API response before upgrading. Convert such
  code to `Annotation::REFERENCE_TYPES`, which is now an `alias => class` map,
  or to `$model->getMorphClass()`.

  Writes are compatible: a submitted class name — in any letter case, with or
  without a leading backslash — is still accepted and is normalized to the
  alias, so an existing API client keeps working without a change.

  `Annotation::REFERENCE_TYPES` changed shape with it, from a list of classes to
  an `alias => class` map. `in_array($class, …, true)` and `foreach (… as
  $class)` still work; an indexed read such as `REFERENCE_TYPES[0]` does not.

  **The morph map is application-global.** It changes `getMorphClass()` for
  `Paragraph` and `Sentence` everywhere in the host application, not only inside
  `b_annotations` — and only `b_annotations` gets a data migration. If any table
  of your own stores these class names in a morph type column, its relations
  will silently stop matching until you rewrite them. The upgrade notes give the
  audit and the `UPDATE`.

  A data migration rewrites existing rows. Rows holding a value the package
  never wrote are left untouched: they cannot be distinguished from a host
  application's own discriminator, and the model already refuses to resolve an
  impermissible one. Auditing them is the operator's decision. Rolling the
  migration back is for downgrading to 2.x — doing it while still running 3.0.0
  leaves the code querying aliases against class names.

  The map is registered with `Relation::morphMap()`, not
  `Relation::enforceMorphMap()`, so no `requireMorphMap()` flag is imposed on
  the host application. A host that registers its own alias for either model
  from a service provider's `boot()` takes precedence, because package providers
  boot first. The package claims the `b_paragraphs` and `b_sentences` aliases; a
  host that has already given either name to one of its own models will find
  those annotations start raising `InvalidReferenceTypeException` on read, and
  Laravel reports no conflict — check before upgrading.

  This is the second half of the work begun in
  [#13](https://github.com/ThreeLeaf-com/Biblioteca/issues/13). The security
  fix itself shipped in 2.2.0 with no shape change, so `^2.0` consumers receive
  it on a routine update; only this cosmetic and structural change requires the
  major bump.
  ([#14](https://github.com/ThreeLeaf-com/Biblioteca/issues/14))

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
