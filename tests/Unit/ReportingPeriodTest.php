<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use LibreCode\UsageStatistics\ReportingPeriod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReportingPeriodTest extends TestCase {
	public function testNormalizesBothBoundariesToUtc(): void {
		$period = new ReportingPeriod(
			new DateTimeImmutable('2026-08-01T03:00:00+03:00'),
			new DateTimeImmutable('2026-08-02T03:00:00+03:00'),
		);

		self::assertSame([
			'start' => '2026-08-01T00:00:00Z',
			'end' => '2026-08-02T00:00:00Z',
		], $period->toArray());
	}

	#[DataProvider('calendarMonths')]
	public function testCreatesCalendarMonthAtUtcBoundary(string $instant, string $expectedStart, string $expectedEnd): void {
		$period = ReportingPeriod::monthContaining(new DateTimeImmutable($instant));

		self::assertSame($expectedStart, $period->toArray()['start']);
		self::assertSame($expectedEnd, $period->toArray()['end']);
	}

	/** @return iterable<string,array{string,string,string}> */
	public static function calendarMonths(): iterable {
		yield 'timezone crosses into next UTC month' => [
			'2026-02-28T23:59:59-03:00',
			'2026-03-01T00:00:00Z',
			'2026-04-01T00:00:00Z',
		];
		yield 'leap-year february' => [
			'2028-02-15T12:34:56Z',
			'2028-02-01T00:00:00Z',
			'2028-03-01T00:00:00Z',
		];
		yield 'december rolls into next year' => [
			'2026-12-31T23:59:59Z',
			'2026-12-01T00:00:00Z',
			'2027-01-01T00:00:00Z',
		];
	}

	public function testAcceptsExactly31Days(): void {
		$period = new ReportingPeriod(
			new DateTimeImmutable('2026-01-01T00:00:00Z'),
			new DateTimeImmutable('2026-02-01T00:00:00Z'),
		);

		self::assertSame('2026-02-01T00:00:00Z', $period->toArray()['end']);
	}

	#[DataProvider('invalidPeriods')]
	public function testRejectsInvalidPeriod(string $start, string $end, string $message): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage($message);
		new ReportingPeriod(new DateTimeImmutable($start), new DateTimeImmutable($end));
	}

	/** @return iterable<string,array{string,string,string}> */
	public static function invalidPeriods(): iterable {
		yield 'longer than 31 days' => [
			'2026-01-01T00:00:00Z',
			'2026-02-01T00:00:01Z',
			'Reporting period must not exceed 31 days.',
		];
		yield 'zero length' => [
			'2026-08-01T00:00:00Z',
			'2026-08-01T00:00:00Z',
			'Reporting period end must be after start.',
		];
		yield 'reversed' => [
			'2026-08-02T00:00:00Z',
			'2026-08-01T00:00:00Z',
			'Reporting period end must be after start.',
		];
	}
}
