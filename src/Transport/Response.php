<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Transport;

final class Response {
	/** @var array<string,string> */
	public readonly array $headers;

	/** @param array<string,string> $headers */
	public function __construct(
		public readonly int $statusCode,
		public readonly string $body,
		array $headers = [],
	) {
		$normalized = [];
		foreach ($headers as $name => $value) {
			$normalized[strtolower($name)] = $value;
		}
		$this->headers = $normalized;
	}

	public function header(string $name): ?string {
		return $this->headers[strtolower($name)] ?? null;
	}
}
