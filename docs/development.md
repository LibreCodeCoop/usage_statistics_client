<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Development

Install dependencies:

```bash
composer install
```

Run the main checks:

```bash
composer qa
composer audit
```

Run coverage when Xdebug or PCOV coverage is available:

```bash
composer test:coverage
```

Validate namespace prefixing:

```bash
rm -rf build/scoped
vendor/bin/php-scoper add-prefix --prefix=UsageStatisticsClientScoped --output-dir=build/scoped --force src
php tests/scoping-smoke.php
```

Commits use Conventional Commits and Developer Certificate of Origin sign-off, for example:

```bash
git commit -s -m 'feat: add report submission client'
```
