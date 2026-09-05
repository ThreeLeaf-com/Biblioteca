---
type: Architecture
title: System Overview
description: What the Biblioteca package provides, what the host application must supply, and how the two connect.
resource: src/Providers/BibliotecaServiceProvider.php
tags: [architecture, laravel, package]
timestamp: 2026-09-04T00:00:00Z
---

# System Overview

Biblioteca is a **library package**, not an application. It is installed with
Composer into a host Laravel application. It has no HTTP kernel, no
authentication, no user interface, and no deployment target of its own.

## What the package supplies

| Area          | Location                                               |
| ------------- | ------------------------------------------------------ |
| Models        | [`src/Models/`](../../../src/Models)                   |
| Migrations    | [`database/migrations/`](../../../database/migrations) |
| Factories     | [`database/factories/`](../../../database/factories)   |
| Repositories  | [`src/Repositories/`](../../../src/Repositories)       |
| Services      | [`src/Services/`](../../../src/Services)               |
| HTTP layer    | [`src/Http/`](../../../src/Http)                       |
| Example route | [`routes/api.php`](../../../routes/api.php)            |
| Enums         | [`src/Enums/`](../../../src/Enums)                     |
| Traits        | [`src/Traits/`](../../../src/Traits)                   |
| Utilities     | [`src/Utils/`](../../../src/Utils)                     |

## What the host application supplies

- A database connection. The package declares `ext-pdo` and
  `illuminate/database`; it does not choose an engine.
- Route registration. [`routes/api.php`](../../../routes/api.php) is an example
  file. The host application decides whether to load it, where to mount it, and
  what middleware to apply. See
  [Authorization Boundary](/security/authorization-boundary.md).
- Authentication and authorization. The package has none.

## Service provider

[`BibliotecaServiceProvider`](../../../src/Providers/BibliotecaServiceProvider.php)
is auto-discovered through the `extra.laravel.providers` entry in
[`composer.json`](../../../composer.json). It does one thing in `boot()`: it
calls `loadMigrationsFrom()` on the package migration directory, so the host
application picks up the schema with a normal `php artisan migrate`. The
`register()` method binds nothing.

## Framework support

[`composer.json`](../../../composer.json) requires PHP `>=8.2` and
`illuminate/database` and `illuminate/support` at `^12.0 || ^13.0`. CI proves
both Laravel majors — see [Testing Strategy](/testing/strategy.md).

## Table namespace

Every table this package creates carries a `b_` prefix, which keeps the package
schema from colliding with host-application tables. The models build their table
names from
[`BibliotecaConstants::TABLE_PREFIX`](../../../src/Constants/BibliotecaConstants.php);
the migration hardcodes the same prefix as literals.
See [Database Schema](/data/models/database-schema.md).

# Citations

- Verified 2026-09-04 against git HEAD — `src/Providers/BibliotecaServiceProvider.php`
  `boot()` calls only `loadMigrationsFrom()`; `register()` has an empty body.
- Verified 2026-09-04 against git HEAD — `composer.json` `require` block and
  `extra.laravel.providers` entry.
- Verified 2026-09-04 against git HEAD — the header comment of `routes/api.php`
  states the file is an example only.
