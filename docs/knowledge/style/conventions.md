---
type: Style Guide
title: Conventions
description: Naming, PHPDoc, annotation, and migration-comment rules this package follows.
resource: src
tags: [style, conventions, phpdoc, documentation]
timestamp: 2026-09-28T00:00:00Z
---

# Conventions

The conventions below are the ones a reader of this codebase will notice first,
and the ones a contributor is expected to follow.

## Naming

- **Namespace root** is `ThreeLeaf\Biblioteca\`, mapped to `src/` by PSR-4.
- **Tables** carry the `b_` prefix from `BibliotecaConstants::TABLE_PREFIX`, and
  each model exposes its own `TABLE_NAME` constant. Reference the constant, not
  the literal, wherever a table name is needed — validation rules do this.
  - **Exception: `Paragraph::TABLE_NAME` and `Sentence::TABLE_NAME` are also
    morph aliases**, and a morph alias is persisted data. Renaming either table
    would change what `Annotation.reference_type` stores and orphan every
    existing row. A test pins both to their literal values so a rename fails
    loudly; treat it as a data-format change requiring its own migration, not a
    rename. See [Domain Model](/data/models/domain-model.md).
  - **Migrations are the other exception**: they name tables and classes as
    literals, because a migration is replayed by every fresh install and must
    keep doing what it did when it was written.
- **Primary keys** are named after the entity (`book_id`, `chapter_id`,
  `toc_id`), never `id`.
- **Route parameters** match the key name: `books/{book_id}`.

## PHPDoc

Every class carries a docblock. Beyond that:

- Models declare the full `@property` and `@property-read` block for every
  column and relationship, with `@property Carbon` for date columns and
  `@mixin Builder` on the class.
- Methods document `@param`, `@return`, and `@throws` with prose, not just
  types — repository read methods, for example, state which variant returns
  `null` and which throws `ModelNotFoundException`.
- Cross-references use the `{@link ClassName}` form.
- Traits document their contract, including any property the using class is
  expected to declare — `HasCompositeKey` documents `$primaryKeys`.

## OpenAPI attributes

Models, form requests, resources, controllers, and enums carry `#[OA\...]`
attributes below their PHPDoc, imported as `use OpenApi\Attributes as OA;`.
Controller tags are namespaced `Biblioteca/<Entity>`. These attributes are
load-bearing, not decorative: they are the input to
[OpenAPI Generation](/features/openapi-generation.md).

Do **not** write the docblock `@OA\*` form. swagger-php reads it only when
`doctrine/annotations` is installed, which this package does not do, so a
docblock annotation is silently ignored.

## Migration comments

Every table gets `$table->comment(...)` and **every column** gets
`->comment(...)`. The comment mirrors the intent recorded in the model's PHPDoc,
so the schema is self-describing when read through a database client rather than
through the code. Timestamps use the fixed form:

```php
$table->timestamp(Model::CREATED_AT)->useCurrent()->comment('...');
$table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('...');
```

See [Database Schema](/data/models/database-schema.md).

## Validation rules

Rules are arrays of strings, not pipe-delimited strings, so a rule object such
as `Rule::unique()` can sit alongside plain rules. See
[Input Validation](/security/input-validation.md).

## Dependencies

Services and repositories are injected as promoted `private readonly`
constructor properties. See [Layering](/architecture/layering.md).

## HTTP status codes

Controllers import
`Symfony\Component\HttpFoundation\Response as HttpCodes` and use its constants
rather than integer literals.

## Documentation

Markdown in this bundle follows OKF conventions: bundle-root-relative links
between concepts, relative paths to in-repo files outside the bundle, and a
`# Citations` section recording what was verified and when. Terms are defined in
[Glossary](/style/glossary.md).

## Permanent documentation stands on its own: no tickets, PRs, local links, or personal PII

Code comments, docblocks, `docs/knowledge/` concepts, User Guides, DevOps runbooks, and any other committed documentation must make sense to a reader who has nothing but the repository. Do not put any of these in them:

- **Issue IDs or issue tracker links:** GitHub issue numbers (`#123`), Jira keys (`TB-###`, `TL-123`), or direct links to issues.
- **Pull request numbers or links:** GitHub PR numbers (`#456`), PR URLs, or review threads.
- **Commit hashes:** Short or full SHAs (`abc1234`). Cite the source file and line range the claim rests on instead.
- **Links to ephemeral or local-only material:** gitignored folders such as `work-items/`, scratchpad or temp paths, CI run URLs that expire, session identifiers. Anything that will not resolve for a future reader on a clean checkout.
- **Personal PII (Personally Identifiable Information):** Individual people's real names, personal email addresses, phone numbers, Slack IDs, or @-handles. Name the role or actor instead ("the team lead", "the assignee", "the reporter", "the customer"). Conventional placeholders (`John Doe`, `Jane Smith`, `user@example.com`) are allowed for illustrative examples.

Links to public external documentation, such as a framework's, language's, or vendor's official docs (e.g., Laravel, PHP, Python, Swift, MDN, RFCs), are allowed and encouraged: they resolve for every reader and outlive any single ticket.

State the fact itself, and explain _why_ in full. If a reader needs history, `git log` and `git blame` on the line already carry the commit and its ticket key, and the ticket and pull request are where discussion belongs. Cite evidence as source paths and line ranges. A reference that only makes sense to someone who remembers the ticket is noise to everyone else.

### Live routing exception: scheduled TODO and FIXME

A `TODO` or `FIXME` for work that is already scheduled may name the ticket that will do it, because there the key is live routing information rather than history. Write it in exactly this form, so every exception can be found with one search:
- GitHub issues: `// TODO(#123): problem + brief plan` or `FIXME(#123): …`
- Jira issues: `// TODO(TB-123): problem + brief plan` or `FIXME(TB-123): …`
- Test impasse (`test-diagnosis` skill): `FIXME(test-diagnosis): symptom + plan` (ticket lives in `TEST_PUNCH_LIST.md` / work-item, not in the comment).

Every `TODO`/`FIXME` must carry an explanation in addition to the ticket reference. A bare ticket ID or an unticketed `TODO` both fail review. An existing `TODO` or `FIXME` in another form is corrected when its line is next edited.

### Where tracking and attribution belong

Commit messages, pull request titles/descriptions, issue tracker tickets, and gitignored `work-items/` folders are where ticket keys and author discussions belong; this rule does not apply to them.

Ownership metadata files whose explicit purpose requires identity (`CODEOWNERS`, root `README.md` attribution, `agents/.agent-config.json` machine config, package lockfiles) are exempt for PII. Placeholder keys used to illustrate a format (such as `#${TICKET}` or `[TB-123] fix: …` in commit conventions) are not references.

### Fix as you go

Existing documentation predates this rule and is not swept. Whenever you edit a line that carries one of these references or personal PII, remove it from that line and reword so the line still reads correctly. Leave lines you did not otherwise touch alone.

# Citations

- Verified 2026-09-04 against git HEAD — PSR-4 root and namespace read from
  `composer.json`; `BibliotecaConstants::TABLE_PREFIX` is `'b_'`.
- Verified 2026-09-07 against git HEAD — `Book` carries the `@property` block,
  `@mixin Builder`, and an `#[OA\Schema]` attribute; `HasCompositeKey` documents
  `$primaryKeys`.
- Verified 2026-09-04 against git HEAD — every `Schema::create` block in the
  migration calls `$table->comment()` and comments each column.
- Verified 2026-09-04 against git HEAD — `AuthorController` imports
  `Symfony\Component\HttpFoundation\Response as HttpCodes`.
