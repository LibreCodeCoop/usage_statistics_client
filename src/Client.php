<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics;

use InvalidArgumentException;
use JsonException;
use LibreCode\UsageStatistics\Exception\ProtocolException;
use LibreCode\UsageStatistics\Exception\ServerRejectedException;
use LibreCode\UsageStatistics\Transport\TransportInterface;

final class Client {
	public function __construct(
		private readonly TransportInterface $transport,
		private readonly Endpoint $endpoint,
		private readonly float $timeoutSeconds = 5.0,
	) {
		if ($timeoutSeconds <= 0) {
			throw new InvalidArgumentException('Timeout must be greater than zero.');
		}
	}

	public function submit(Report $report, ConsentState $consent): SubmissionResult {
		if ($consent !== ConsentState::Enabled) {
			return SubmissionResult::SkippedWithoutConsent;
		}

		try {
			$payload = json_encode($report->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
		} catch (JsonException $e) {
			throw new ProtocolException('Unable to serialize report.', 0, $e);
		}

		$response = $this->transport->request(
			'POST',
			$this->endpoint->url,
			[
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
			],
			$payload,
			$this->timeoutSeconds,
		);

		if ($response->statusCode !== 200) {
			[$errorCode, $message] = $this->parseError($response->body);
			throw new ServerRejectedException(
				$response->statusCode,
				$errorCode,
				$message ?? 'Usage statistics server rejected the report.',
				$response->header('retry-after'),
			);
		}

		try {
			$body = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			throw new ProtocolException('Usage statistics server returned invalid JSON.', 0, $e);
		}

		if (!is_array($body) || ($body['status'] ?? null) !== 'accepted') {
			throw new ProtocolException('Usage statistics server returned an unexpected success response.');
		}

		return SubmissionResult::Accepted;
	}

	/** @return array{0:?string,1:?string} */
	private function parseError(string $body): array {
		try {
			$decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException) {
			return [null, null];
		}

		if (!is_array($decoded)) {
			return [null, null];
		}

		$error = null;
		if (isset($decoded['error']) && is_string($decoded['error'])) {
			$error = $decoded['error'];
		}

		$message = null;
		if (isset($decoded['message']) && is_string($decoded['message'])) {
			$message = $decoded['message'];
		}

		return [$error, $message];
	}
}
