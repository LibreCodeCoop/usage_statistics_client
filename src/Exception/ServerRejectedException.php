<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Exception;

use RuntimeException;

final class ServerRejectedException extends RuntimeException {
	public function __construct(
		public readonly int $statusCode,
		public readonly ?string $errorCode = null,
		string $message = 'Usage statistics server rejected the report.',
		public readonly ?string $retryAfter = null,
	) {
		parent::__construct($message);
	}

	public function isTransient(): bool {
		return $this->statusCode === 429 || $this->statusCode >= 500;
	}
}
