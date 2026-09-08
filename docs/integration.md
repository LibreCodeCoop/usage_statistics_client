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
