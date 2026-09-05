---
title: Security Concepts
description: Index of the security controls documented for the Biblioteca package, and the boundary of what the package does and does not protect.
---

# Security Concepts

Biblioteca is a library, so its security posture is mostly a statement about
what it deliberately leaves to the host application.

## Concepts

- [Authorization Boundary](/security/authorization-boundary.md) — the package
  ships no authentication and no authorization. This concept states exactly what
  the host application must add before exposing the routes.
- [Input Validation](/security/input-validation.md) — the one protection the
  package does implement, through Laravel form requests, and the limits of what
  it covers.

## Out of scope for this package

| Concern                  | Owned by                                       |
| ------------------------ | ---------------------------------------------- |
| Authentication           | Host application                               |
| Authorization and roles  | Host application                               |
| Rate limiting            | Host application middleware                    |
| Transport security (TLS) | Host application deployment                    |
| Secret storage           | Host application; the package reads no secrets |
| CORS                     | Host application                               |

The package declares no configuration file, reads no environment variables, and
makes no outbound network calls. See
[System Overview](/architecture/system-overview.md).
