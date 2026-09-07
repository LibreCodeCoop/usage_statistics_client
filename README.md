# Usage Statistics Client

Reusable PHP/Composer client primitives for applications that want to collect and submit opt-in, privacy-preserving usage statistics.

The package is designed to be application-agnostic. Applications own their metric definitions, consent experience, and default receiving endpoint.

## Responsibilities

The client package is expected to provide:

- provider contracts for application-defined metrics;
- report and metric value objects;
- reporting-period helpers;
- consent-state persistence abstractions;
- pseudonymous installation identifier helpers;
- endpoint resolution with application default plus administrator override;
- payload validation and serialization;
- HTTPS report submission;
- last-successful-send state;
- helpers for background jobs and OCC commands in Nextcloud applications.

## Explicit non-responsibilities

The package does not:

- decide which metrics an application should collect;
- provide a global LibreSign endpoint;
- silently enable reporting;
- own application-specific settings pages;
- require or integrate with Nextcloud `survey_client`;
- claim to verify the truthfulness of values reported by a self-hosted client.

## Consent model

The planned shared consent state is tri-state:

- `unknown`: no decision has been recorded;
- `enabled`: the administrator opted in;
- `disabled`: the administrator opted out.

Applications decide when and how to present the initial request. Once a decision is recorded, the client library must not independently prompt again.

## Protocol

The initial implementation targets Usage Statistics Protocol v1 defined by the server project.

## Status

Early design and implementation.
