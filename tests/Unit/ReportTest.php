<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use LibreCode\UsageStatistics\Metric;
use LibreCode\UsageStatistics\Report;
use LibreCode\UsageStatistics\ReportingPeriod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReportTest extends TestCase {
	public function testSerializesExactProtocolV1Payload(): void {
		$report = new Report(
			'libresign',
			str_repeat('a', 64),
			2,
			new ReportingPeriod(
				new DateTimeImmutable('2026-08-01T00:00:00Z'),
				new DateTimeImmutable('2026-09-01T00:00:00Z'),
			),
			[
				Metric::string('environment', 'version', '12.0.0'),
				Metric::integer('usage', 'requests_completed', 72),
			],
		);

		self::assertSame([
			'protocolVersion' => 1,
			'application' => 'libresign',
			'installationId' => str_repeat('a', 64),
			'schemaVersion' => 2,
			'period' => [
				'start' => '2026-08-01T00:00:00Z',
				'end' => '2026-09-01T00:00:00Z',
			],
			'metrics' => [
				['category' => 'environment', 'key' => 'version', 'type' => 'string', 'value' => '12.0.0'],
				['category' => 'usage', 'key' => 'requests_completed', 'type' => 'integer', 'value' => 72],
			],
		], $report->toArray());
	}

	public function testAcceptsProtocolMaximumOf256Metrics(): void {
		$metrics = [];
		for ($i = 0; $i < 256; ++$i) {
			$metrics[] = Metric::integer('usage', 'metric_' . $i, $i);
		}

		$report = new Report('libresign', str_repeat('a', 64), 1, $this->period(), $metrics);
		self::assertCount(256, $report->metrics);
	}

	public function testRejectsMoreThan256Metrics(): void {
		$metrics = [];
		for ($i = 0; $i < 257; ++$i) {
			$metrics[] = Metric::integer('usage', 'metric_' . $i, $i);
		}

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Report must contain between 1 and 256 metrics.');
		new Report('libresign', str_repeat('a', 64), 1, $this->period(), $metrics);
	}

	public function testRejectsDuplicateMetricIdentityRegardlessOfValue(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Duplicate metric category/key pair.');
		new Report('libresign', str_repeat('a', 64), 1, $this->period(), [
			Metric::integer('usage', 'count', 1),
			Metric::integer('usage', 'count', 2),
		]);
	}

	public function testRejectsEmptyMetricSet(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Report must contain between 1 and 256 metrics.');
		new Report('libresign', str_repeat('a', 64), 1, $this->period(), []);
	}

	#[DataProvider('invalidSchemaVersions')]
	public function testRejectsInvalidSchemaVersion(int $version): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Schema version must be a positive integer.');
		new Report('libresign', str_repeat('a', 64), $version, $this->period(), [Metric::integer('usage', 'count', 1)]);
	}

	/** @return iterable<string,array{int}> */
	public static function invalidSchemaVersions(): iterable {
		yield 'zero' => [0];
		yield 'negative' => [-1];
	}

	public function testAcceptsMaximumLengthIdentifiers(): void {
		$report = new Report(
			str_repeat('a', 128),
			str_repeat('b', 128),
			1,
			$this->period(),
			[Metric::integer('usage', 'count', 1)],
		);

		self::assertSame(128, strlen($report->application));
		self::assertSame(128, strlen($report->installationId));
	}

	#[DataProvider('invalidReportIdentifiers')]
	public function testRejectsInvalidReportIdentifiers(string $application, string $installationId, string $message): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage($message);
		new Report($application, $installationId, 1, $this->period(), [Metric::integer('usage', 'count', 1)]);
	}

	/** @return iterable<string,array{string,string,string}> */
	public static function invalidReportIdentifiers(): iterable {
		yield 'application alphabet' => ['libresign app', str_repeat('a', 64), 'Application is invalid.'];
		yield 'application too long' => [str_repeat('a', 129), str_repeat('b', 64), 'Application is invalid.'];
		yield 'installation id alphabet' => ['libresign', 'installation id', 'Installation ID is invalid.'];
		yield 'installation id too long' => ['libresign', str_repeat('b', 129), 'Installation ID is invalid.'];
	}

	private function period(): ReportingPeriod {
		return new ReportingPeriod(
			new DateTimeImmutable('2026-08-01T00:00:00Z'),
			new DateTimeImmutable('2026-09-01T00:00:00Z'),
		);
	}
}
