---
okf_version: "0.1"
title: Biblioteca Technical Manual
description: OKF knowledge bundle for the Biblioteca Laravel package — architecture, data model, API, security, testing, style, and feature concepts.
---

# Biblioteca Technical Manual

Biblioteca is a Laravel package that supplies an Eloquent model framework for
written material: authors, publishers, series, books, chapters, paragraphs,
sentences, notes, figures, indices, and annotations. It ships models,
migrations, factories, repositories, services, form requests, API resources,
controllers, and an example API route file.

This bundle is the technical source of truth. The
[User Guide](../user-guide/README.md) covers the other audience: how an
application developer installs and uses the package.

## Architecture

- [System Overview](/architecture/system-overview.md) — what the package is,
  what it does not do, and how it plugs into a host Laravel application.
- [Layering](/architecture/layering.md) — the controller, service, repository,
  and model layers, and which entities use which of them.

## Data

- [Domain Model](/data/models/domain-model.md) — the entities and the
  relationships between them.
- [Database Schema](/data/models/database-schema.md) — the `b_`-prefixed tables,
  their keys, and their foreign-key cascade behaviour.

## API

- [REST Endpoints](/api/rest-endpoints.md) — the example route file, the
  resource endpoints it declares, and the request and response contracts.

## Security

- [Security Concepts](/security/index.md) — index of the security controls.
- [Authorization Boundary](/security/authorization-boundary.md) — why the
  package ships no authentication and what the host application must add.
- [Input Validation](/security/input-validation.md) — how form requests
  constrain incoming data.

## Testing

- [Testing Strategy](/testing/strategy.md) — the PHPUnit suites, the coverage
  report, the compatibility matrix run in CI, how to verify tests by mutation,
  and why foreign keys are not enforced in feature tests.

## Style

- [Conventions](/style/conventions.md) — naming, PHPDoc, and migration comment
  rules this package follows.
- [Glossary](/style/glossary.md) — shared terms used across the bundle.

## Features

- [OpenAPI Generation](/features/openapi-generation.md) — how `target/api-docs.json`
  is produced from source annotations.
- [Chapter Text Parsing](/features/chapter-text-parsing.md) — how chapter
  content is split into paragraphs and sentences.
- [UUID Identifiers](/features/uuid-identifiers.md) — the identifier scheme,
  including deterministic UUIDs.
