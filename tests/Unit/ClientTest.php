<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase {
	#[DataProvider('invalidTimeouts')]
	public function testRejectsInvalidTimeout(float $timeout): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Timeout must be greater than zero.');
		new Client(
			new RecordingTransport(new Response(200, '{"status":"accepted"}')),
			new Endpoint('https://stats.example/api/v1/reports'),
			$timeout,
		);
	}

	/** @return iterable<string,array{float}> */
	public static function invalidTimeouts(): iterable {
		yield 'zero' => [0.0];
		yield 'negative' => [-0.1];
	}

	#[DataProvider('consentStatesWithoutSubmission')]
	public function testDoesNotSendWithoutEnabledConsent(ConsentState $consent): void {
		$transport = new RecordingTransport(new Response(200, '{"status":"accepted"}'));
		$client = new Client($transport, new Endpoint('https://stats.example/api/v1/reports'));

		self::assertSame(SubmissionResult::SkippedWithoutConsent, $client->submit($this->report(), $consent));
		self::assertSame(0, $transport->calls);
	}

	/** @return iterable<string,array{ConsentState}> */
	public static function consentStatesWithoutSubmission(): iterable {
		yield 'unknown' => [ConsentState::Unknown];
		yield 'disabled' => [ConsentState::Disabled];
	}

	public function testSendsProtocolPayloadAndRequiredHeadersWhenEnabled(): void {
		$transport = new RecordingTransport(new Response(200, '{"status":"accepted"}'));
		$client = new Client($transport, new Endpoint('https://stats.example/api/v1/reports'), 2.5);

		self::assertSame(SubmissionResult::Accepted, $client->submit($this->report(), ConsentState::Enabled));
		self::assertSame('POST', $transport->method);
		self::assertSame('https://stats.example/api/v1/reports', $transport->url);
		self::assertSame(2.5, $transport->timeout);
		self::assertSame([
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		], $transport->headers);

		$payload = json_decode($transport->body, true, 512, JSON_THROW_ON_ERROR);
		self::assertIsArray($payload);
		self::assertSame(1, $payload['protocolVersion']);
		self::assertSame('libresign', $payload['application']);
	}

	public function testDoesNotEscapeSlashesInMetricValues(): void {
		$transport = new RecordingTransport(new Response(200, '{"status":"accepted"}'));
		$client = new Client($transport, new Endpoint('https://stats.example/api/v1/reports'));
		$report = $this->report([Metric::string('environment', 'documentation', 'https://docs.example/path')]);

		$client->submit($report, ConsentState::Enabled);

		self::assertStringContainsString('https://docs.example/path', $transport->body);
		self::assertStringNotContainsString('https:\\/\\/docs.example', $transport->body);
	}

	#[DataProvider('serverRejections')]
	public function testMapsServerRejections(
		int $statusCode,
		string $body,
		?string $expectedErrorCode,
		string $expectedMessage,
		bool $expectedTransient,
		?string $retryAfter,
	): void {
		$headers = $retryAfter === null ? [] : ['retry-after' => $retryAfter];
		$client = new Client(
			new RecordingTransport(new Response($statusCode, $body, $headers)),
			new Endpoint('https://stats.example/api/v1/reports'),
		);

		try {
			$client->submit($this->report(), ConsentState::Enabled);
			self::fail('Expected exception.');
		} catch (ServerRejectedException $e) {
			self::assertSame($statusCode, $e->statusCode);
			self::assertSame($expectedErrorCode, $e->errorCode);
			self::assertSame($expectedMessage, $e->getMessage());
			self::assertSame($expectedTransient, $e->isTransient());
			self::assertSame($retryAfter, $e->retryAfter);
		}
	}

	/** @return iterable<string,array{int,string,?string,string,bool,?string}> */
	public static function serverRejections(): iterable {
		yield 'validation error' => [
			400,
			'{"error":"invalid_report","message":"Application schema is not registered."}',
			'invalid_report',
			'Application schema is not registered.',
			false,
			null,
		];
		yield 'schema conflict' => [
			409,
			'{"error":"conflicting_report","message":"Report period already exists with another schema."}',
			'conflicting_report',
			'Report period already exists with another schema.',
			false,
			null,
		];
		yield 'rate limited' => [
			429,
			'{"error":"rate_limited"}',
			'rate_limited',
			'Usage statistics server rejected the report.',
			true,
			'60',
		];
		yield 'server error' => [
			500,
			'{"error":"server_error"}',
			'server_error',
			'Usage statistics server rejected the report.',
			true,
			null,
		];
		yield 'unstructured gateway error' => [
			502,
			'gateway failure',
			null,
			'Usage statistics server rejected the report.',
			true,
			null,
		];
		yield 'non-string error fields are ignored' => [
			400,
			'{"error":12,"message":false}',
			null,
			'Usage statistics server rejected the report.',
			false,
			null,
		];
	}

	#[DataProvider('invalidSuccessBodies')]
	public function testRejectsInvalidSuccessResponse(string $body): void {
		$client = new Client(
			new RecordingTransport(new Response(200, $body)),
			new Endpoint('https://stats.example/api/v1/reports'),
		);
		$this->expectException(ProtocolException::class);
		$client->submit($this->report(), ConsentState::Enabled);
	}

	/** @return iterable<string,array{string}> */
	public static function invalidSuccessBodies(): iterable {
		yield 'unexpected status' => ['{"status":"different"}'];
		yield 'missing status' => ['{}'];
		yield 'non-object json' => ['[]'];
		yield 'invalid json' => ['not-json'];
	}

	/** @param list<Metric>|null $metrics */
	private function report(?array $metrics = null): Report {
		return new Report(
			'libresign',
			str_repeat('a', 64),
			1,
			new ReportingPeriod(
				new DateTimeImmutable('2026-08-01T00:00:00Z'),
				new DateTimeImmutable('2026-09-01T00:00:00Z'),
			),
			$metrics ?? [Metric::integer('usage', 'requests_completed', 72)],
		);
	}
}
