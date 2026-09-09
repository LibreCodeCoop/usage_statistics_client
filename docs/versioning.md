<!--
SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
SPDX-License-Identifier: AGPL-3.0-or-later
-->

# Versioning

The package follows Semantic Versioning.

Public classes, enums, interfaces, constructor signatures, return types, exception contracts, and serialized Protocol v1 behavior are part of the supported API once a stable release is published.

A protocol schema version is not a package version. Applications can update their metric schema independently by sending the appropriate `schemaVersion` registered on the server.

Protocol evolution must be explicit. A future Protocol v2 must not silently change Protocol v1 serialization or response handling.

Deprecated public APIs should remain functional for at least one normal minor-release migration window before removal in the next major release, unless a security issue requires faster action.
