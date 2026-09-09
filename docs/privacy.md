<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Privacy model

Protocol v1 is for opt-in aggregate usage statistics. It is not a user-event tracking protocol.

Applications must not submit names, email addresses, user IDs, document or file names, document contents, raw instance URLs or hostnames, authentication material, secrets, IP addresses as metric values, or individual user event streams.

`InstallationId::derive()` hashes a domain-separated combination of the application ID and a host-provided local installation identifier. This makes the resulting 64-character identifier stable for the same application and local installation while preventing the raw identifier from being sent. Different application IDs produce different derived identifiers for the same local input.

This is pseudonymization, not anonymization. If the local input has low entropy and becomes known to an observer, it may be guessable. Applications should use a stable, non-public installation identifier as input and must not pass a hostname, URL, email, user ID, token, or secret as the report identifier itself.

Transport infrastructure can still observe metadata such as source IP addresses. Server logging and retention are separate operational concerns.
