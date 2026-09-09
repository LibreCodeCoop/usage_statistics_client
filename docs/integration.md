<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Application integration

The host application owns five decisions: its stable application ID, schema version, metric definitions, consent persistence, and report endpoint.

## Consent

Persist consent in the host application's normal settings store. Translate it to `ConsentState` immediately before submission. `unknown` and `disabled` both cause `Client::submit()` to return `SubmissionResult::SkippedWithoutConsent` without invoking the transport.

The library never prompts a user and never changes consent state.

## Endpoint

Pass the complete Protocol v1 report URL to `Endpoint`. It must use HTTPS and must not contain embedded credentials, a query string, or a fragment. Applications may ship their own default and expose an administrator override.

Do not put a LibreSign-specific default in shared library code.

## Scheduling

Run reporting from the host application's background-job mechanism. A monthly calendar period is the initial recommended cadence. `ReportingPeriod::monthContaining()` is provided for that common case.

The client does not retry internally. On `TransportException` or `ServerRejectedException::isTransient() === true`, let a later scheduled execution decide whether to retry. This keeps telemetry failures outside latency-sensitive application paths.

## Transport adapters

A framework can implement `TransportInterface` around its existing HTTP client. The adapter receives the method, absolute URL, headers, JSON body, and total timeout, and returns a small `Response` object.

Do not log the request body by default. A malformed host integration could accidentally add sensitive data even though the protocol forbids it.

## LibreSign

LibreSign vendors PHP dependencies through the `LibreSign/3rdparty` repository and prefixes them with PHP-Scoper using `OCA\\Libresign\\Vendor`.

After this package has a published release, add `librecodecoop/usage-statistics-client` to `LibreSign/3rdparty`. The resulting API is used through the scoped namespace, for example `OCA\\Libresign\\Vendor\\LibreCode\\UsageStatistics\\Client`.

The package CI runs a smoke test using that exact prefix so namespace isolation is checked before release.

LibreSign still owns the Nextcloud-specific integration:

- an adapter from the Nextcloud HTTP client to `TransportInterface`;
- persistence and administration of the consent state;
- the `libresign` application ID and active schema version;
- metric definitions and aggregation semantics;
- the default report endpoint and any administrator override;
- the local installation identifier used as input to `InstallationId::derive()`;
- background-job scheduling and retry policy.

The receiving usage-statistics server must have the matching LibreSign application schema registered before reports using that schema version can be accepted.

Until the package has a stable tag available to Composer, LibreSign can only consume it through a temporary VCS/dev dependency. Production integration should use a tagged release.
