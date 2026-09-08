<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Architecture

The package keeps the supported surface deliberately small.

`Metric`, `ReportingPeriod`, `Report`, `InstallationId`, `Endpoint`, `ConsentState`, `Client`, and the transport types form the public API. Application-specific metric definitions, settings pages, persistence, background jobs, OCC commands, and logging stay in the host application.

The flow is:

```text
application metrics -> Report -> consent gate -> JSON serialization -> TransportInterface -> server
```

The client performs stable Protocol v1 validations that catch programming errors before a request: identifier syntax and lengths, metric type/value compatibility, duplicate metrics, positive schema version, metric-count bounds, and the 31-day maximum reporting period. It does not duplicate server-side schema registration or administrative validation.

`TransportInterface` belongs to this package instead of exposing PSR-18/PSR-7. This is intentional: a consumer that prefixes the complete dependency tree must not accidentally make a prefixed PSR interface incompatible with an unprefixed object supplied by the host application.

`StreamTransport` is a zero-dependency default. Consumers may replace it with an adapter around their framework HTTP client.
