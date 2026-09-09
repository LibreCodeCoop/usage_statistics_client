<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics;

use InvalidArgumentException;

final class Endpoint {
	public readonly string $url;

	public function __construct(string $url) {
		$parts = parse_url($url);
		if (
			!is_array($parts)
			|| ($parts['scheme'] ?? null) !== 'https'
			|| !isset($parts['host'])
			|| $parts['host'] === ''
			|| isset($parts['user'])
			|| isset($parts['pass'])
			|| isset($parts['query'])
			|| isset($parts['fragment'])
		) {
			throw new InvalidArgumentException(
				'Report endpoint must be an HTTPS URL without credentials, query, or fragment.',
			);
		}

		$this->url = rtrim($url, '/');
	}

	public function __toString(): string {
		return $this->url;
	}
}
