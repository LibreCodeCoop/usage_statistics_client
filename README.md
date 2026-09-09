<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Usage Statistics Client

Reusable PHP client for applications that submit opt-in usage statistics using [Usage Statistics Protocol v1](https://github.com/LibreCodeCoop/usage_statistics_server/blob/main/docs/protocol-v1.md).

## Requirements

- PHP 8.2 or newer
- Composer

## Install

```bash
composer require librecodecoop/usage-statistics-client
```

## Example

```php
use LibreCode\UsageStatistics\Client;
use LibreCode\UsageStatistics\ConsentState;
use LibreCode\UsageStatistics\Endpoint;
use LibreCode\UsageStatistics\InstallationId;
use LibreCode\UsageStatistics\Metric;
use LibreCode\UsageStatistics\Report;
use LibreCode\UsageStatistics\ReportingPeriod;
use LibreCode\UsageStatistics\Transport\StreamTransport;

$report = new Report(
    application: 'libresign',
    installationId: (string) InstallationId::derive('libresign', $localInstallationIdentifier),
    schemaVersion: 1,
    period: ReportingPeriod::monthContaining(new DateTimeImmutable()),
    metrics: [
        Metric::string('environment', 'version', '12.0.0'),
        Metric::integer('usage', 'requests_completed', 72),
    ],
);

$client = new Client(
    new StreamTransport(),
    new Endpoint('https://statistics.example/api/v1/reports'),
);

$client->submit($report, ConsentState::Enabled);
```

Applications define their own metrics, consent UI and persistence, endpoint, scheduling and logging policy. The client validates and serializes Protocol v1 reports and submits them when consent is enabled.

## Documentation

- [Integration](docs/integration.md)
- [Privacy](docs/privacy.md)
- [Architecture](docs/architecture.md)
- [PHP-Scoper](docs/php-scoper.md)
- [Mozart](docs/mozart.md)
- [Development](docs/development.md)
- [Versioning](docs/versioning.md)
- [Troubleshooting](docs/troubleshooting.md)
- [Contributing](CONTRIBUTING.md)

## License

AGPL-3.0-or-later. See `COPYING` and `LICENSES/AGPL-3.0-or-later.txt`.
