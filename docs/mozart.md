<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Mozart

Mozart and PHP-Scoper solve related packaging problems but do not use the same build model. Mozart is normally configured by the consuming project to copy and prefix selected Composer dependencies into that project's own dependency namespace.

For this package, configure the consumer to treat `vitormattos/usage-statistics-client` as a dependency to be copied and prefix the `LibreCode\\UsageStatistics\\` namespace into the consumer's private vendor namespace.

Do not prefix only third-party dependencies while leaving this package global if the goal is to let two host applications load different client versions in one PHP process. The package namespace itself must also be isolated.

The exact Mozart configuration depends on the consumer's Mozart version and packaging layout, so this repository does not ship a consumer-specific `mozart.json`. The important compatibility property is that the client contains no hard-coded original FQCN strings or unprefixable runtime dependency.
