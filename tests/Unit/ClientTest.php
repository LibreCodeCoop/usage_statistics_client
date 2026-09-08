<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use DateTimeImmutable;
use LibreCode\UsageStatistics\Client;
use LibreCode\UsageStatistics\ConsentState;
use LibreCode\UsageStatistics\Endpoint;
use LibreCode\UsageStatistics\Exception\ProtocolException;
use LibreCode\UsageStatistics\Exception\ServerRejectedException;
use LibreCode\UsageStatistics\Metric;
use LibreCode\UsageStatistics\Report;
use LibreCode\UsageStatistics\ReportingPeriod;
use LibreCode\UsageStatistics\SubmissionResult;
use LibreCode\UsageStatistics\Transport\Response;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase {
	public function testDoesNotSendWithoutEnabledConsent(): void {
		$transport = new RecordingTransport(new Response(200, '{"status":"accepted"}'));
		$client = new Client($transport, new Endpoint('https://stats.example/api/v1/reports'));

		self::assertSame(SubmissionResult::SkippedWithoutConsent, $client->submit($this->report(), ConsentState::Unknown));
		self::assertSame(SubmissionResult::SkippedWithoutConsent, $client->submit($this->report(), ConsentState::Disabled));
		self::assertSame(0, $transport->calls);
	}

	public function testSendsProtocolPayloadWhenEnabled(): void {
		$transport = new RecordingTransport(new Response(200, '{"status":"accepted"}'));
		$client = new Client($transport, new Endpoint('https://stats.example/api/v1/reports'), 2.5);

		self::assertSame(SubmissionResult::Accepted, $client->submit($this->report(), ConsentState::Enabled));
		self::assertSame('POST', $transport->method);
		self::assertSame('https://stats.example/api/v1/reports', $transport->url);
		self::assertSame(2.5, $transport->timeout);
		self::assertSame('application/json', $transport->headers['Content-Type']);
		$payload = json_decode($transport->body, true, 512, JSON_THROW_ON_ERROR);
		if (!is_array($payload)) {
			throw new \UnexpectedValueException('Expected serialized report to be an array.');
		}
		self::assertSame(1, $payload['protocolVersion']);
		self::assertSame('libresign', $payload['application']);
	}

	public function testMapsServerValidationError(): void {
		$transport = new RecordingTransport(new Response(400, '{"error":"invalid_report","message":"Application schema is not registered."}'));
		$client = new Client($transport, new Endpoint('https://stats.example/api/v1/reports'));

		try {
			$client->submit($this->report(), ConsentState::Enabled);
			self::fail('Expected exception.');
		} catch (ServerRejectedException $e) {
			self::assertSame(400, $e->statusCode);
			self::assertSame('invalid_report', $e->errorCode);
			self::assertFalse($e->isTransient());
		}
	}

	public function testExposesTransientServerFailure(): void {
		$transport = new RecordingTransport(new Response(429, '{"error":"rate_limited"}', ['retry-after' => '60']));
		$client = new Client($transport, new Endpoint('https://stats.example/api/v1/reports'));

		try {
			$client->submit($this->report(), ConsentState::Enabled);
			self::fail('Expected exception.');
		} catch (ServerRejectedException $e) {
			self::assertTrue($e->isTransient());
			self::assertSame('60', $e->retryAfter);
		}
	}

	public function testRejectsUnexpectedSuccessResponse(): void {
		$transport = new RecordingTransport(new Response(200, '{"status":"different"}'));
		$client = new Client($transport, new Endpoint('https://stats.example/api/v1/reports'));
		$this->expectException(ProtocolException::class);
		$client->submit($this->report(), ConsentState::Enabled);
	}

	private function report(): Report {
		return new Report(
			'libresign',
			str_repeat('a', 64),
			1,
			new ReportingPeriod(new DateTimeImmutable('2026-08-01T00:00:00Z'), new DateTimeImmutable('2026-09-01T00:00:00Z')),
			[Metric::integer('usage', 'requests_completed', 72)],
		);
	}
}
