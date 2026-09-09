<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Troubleshooting

## Nothing was sent

Check the consent state first. `unknown` and `disabled` deliberately return `SkippedWithoutConsent` without touching the transport.

## `TransportException`

The endpoint could not be reached or the transport could not obtain a valid HTTP response. Keep telemetry failures out of the application's primary operation and let a later background-job execution retry when appropriate.

## `ServerRejectedException`

Inspect `statusCode` and `errorCode`. Protocol v1 currently uses `400 invalid_report` for invalid reports and `409 conflicting_report` when another schema version was already accepted for the same logical reporting period. `429` and `5xx` responses are classified as transient by `isTransient()`.

## `ProtocolException`

The server returned a success response that does not match the Protocol v1 `{"status":"accepted"}` contract or returned invalid JSON.

## Schema registration errors

The client cannot register application schemas. The `(application, schemaVersion)` definition must already exist on the Usage Statistics Server.
