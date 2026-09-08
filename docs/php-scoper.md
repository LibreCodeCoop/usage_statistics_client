<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# PHP-Scoper

The package is intended to tolerate prefixing of its complete namespace. It has no runtime Composer dependency and exposes no third-party interface in its public API.

A minimal build-time smoke command is:

```bash
vendor/bin/php-scoper add-prefix \
  --prefix=OCA\\LibreSign\\Vendor \
  --output-dir=build/scoped \
  --force src
```

A consumer should normally scope the package as part of its complete vendor build rather than scope only `src/` in isolation. Rebuild Composer autoload metadata after producing the scoped tree according to the consuming application's packaging process.

The repository CI runs a smoke build with PHP-Scoper and loads scoped public classes. If a future public API introduces third-party contracts, revisit scoping before releasing that change.
