<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Contributing

Contributions are welcome through GitHub pull requests.

## Development setup

```bash
composer install
```

The root Composer project installs isolated development tools from `vendor-bin/` through `bamarni/composer-bin-plugin`.

Before submitting changes, run:

```bash
composer qa
composer mutation:test
composer audit
```

Tests belong in `tests/Unit/` and should mirror the production path under `src/`. Prefer tests for protocol invariants and externally observable behavior over tests written only to increase coverage.

Use Conventional Commits and sign commits with `git commit -s`.
