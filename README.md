<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Usage Statistics Client

Framework-agnostic PHP client for applications that submit opt-in usage statistics using [Usage Statistics Protocol v1](https://github.com/LibreCodeCoop/usage_statistics_server/blob/main/docs/protocol-v1.md).

The package provides typed report primitives, pseudonymous installation-ID derivation, consent gating, payload serialization, a small HTTP transport boundary, and response/error handling. Applications remain responsible for their metrics, consent UI and persistence, endpoint configuration, scheduling, and logging policy.

## Requirements

- PHP 8.1 or newer
- Composer
- an HTTPS report endpoint compatible with Protocol v1

## Install

```bash
composer require vitormattos/usage-statistics-client
```

## Minimal use

```php
use LibreCode\UsageStatistics\Client;
use LibreCode\UsageStatistics\ConsentState;
use LibreCode\UsageStatistics\Endpoint;
use LibreCode\UsageStatistics\InstallationId;
use LibreCode\UsageStatistics\Metric;
use LibreCode\UsageStatistics\Report;
use LibreCode\UsageStatistics\ReportingPeriod;
use LibreCode\UsageStatistics\Transport\StreamTransport;

$installationId = InstallationId::derive('libresign', $localInstallationIdentifier);

$report = new Report(
    application: 'libresign',
    installationId: (string)$installationId,
    schemaVersion: 1,
    period: ReportingPeriod::monthContaining(new DateTimeImmutable('2026-08-15T00:00:00Z')),
    metrics: [
        Metric::string('environment', 'version', '12.0.0'),
        Metric::integer('usage', 'requests_completed', 72),
    ],
);

$client = new Client(
    new StreamTransport(),
    new Endpoint('https://statistics.example/apps/usage_statistics_server/api/v1/reports'),
);

$result = $client->submit($report, ConsentState::Enabled);
```

`unknown` and `disabled` consent states never send a request. Network and HTTP failures are surfaced to the application; the client deliberately does not retry automatically, so a background scheduler can decide when to try again without blocking normal application work.

## Design constraints

- no Nextcloud or LibreSign runtime dependency;
- no PSR-7/PSR-18 contract exposed in the public API;
- no authentication, signing, or attestation invented beyond Protocol v1;
- no automatic logging of report payloads;
- no user-level event model;
- runtime dependencies are intentionally zero.

The package is designed so its own namespace can be prefixed when bundled into isolated dependency trees such as LibreSign's `3rdparty` directory.

## Documentation

- [Architecture](docs/architecture.md)
- [Integration](docs/integration.md)
- [Privacy](docs/privacy.md)
- [PHP-Scoper](docs/php-scoper.md)
- [Mozart](docs/mozart.md)
- [Versioning](docs/versioning.md)
- [Development](docs/development.md)
- [Troubleshooting](docs/troubleshooting.md)

## License

AGPL-3.0-or-later. See `LICENSES/AGPL-3.0-or-later.txt`.
