<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Contributor notes

This is a small framework-agnostic Protocol v1 client. Keep runtime dependencies and the public API minimal.

- Source: `src/`
- Tests: `tests/`
- Design and integration notes: `docs/`
- Protocol source of truth: `LibreCodeCoop/usage_statistics_server/docs/protocol-v1.md`

Run `composer qa` and `composer audit` before committing. Validate scoping as described in `docs/development.md` when changing namespaces or public contracts.

Use Conventional Commits and `git commit -s`. Do not add application-specific LibreSign/Nextcloud code to the package core.
