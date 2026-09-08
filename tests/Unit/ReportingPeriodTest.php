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
use PHPUnit\Framework\TestCase;

final class ReportingPeriodTest extends TestCase
{
    public function testNormalizesBothBoundariesToUtc(): void
    {
        $period = new ReportingPeriod(
            new DateTimeImmutable('2026-08-01T03:00:00+03:00'),
            new DateTimeImmutable('2026-08-02T03:00:00+03:00'),
        );

        self::assertSame([
            'start' => '2026-08-01T00:00:00Z',
            'end' => '2026-08-02T00:00:00Z',
        ], $period->toArray());
    }

    public function testCreatesCalendarMonthAtUtcBoundary(): void
    {
        $period = ReportingPeriod::monthContaining(new DateTimeImmutable('2026-02-28T23:59:59-03:00'));

        self::assertSame('2026-03-01T00:00:00Z', $period->toArray()['start']);
        self::assertSame('2026-04-01T00:00:00Z', $period->toArray()['end']);
    }

    public function testAcceptsExactly31Days(): void
    {
        $period = new ReportingPeriod(
            new DateTimeImmutable('2026-01-01T00:00:00Z'),
            new DateTimeImmutable('2026-02-01T00:00:00Z'),
        );

        self::assertSame('2026-02-01T00:00:00Z', $period->toArray()['end']);
    }

    public function testRejectsPeriodLongerThan31Days(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ReportingPeriod(
            new DateTimeImmutable('2026-01-01T00:00:00Z'),
            new DateTimeImmutable('2026-02-01T00:00:01Z'),
        );
    }

    public function testRejectsZeroLengthPeriod(): void
    {
        $instant = new DateTimeImmutable('2026-08-01T00:00:00Z');
        $this->expectException(InvalidArgumentException::class);
        new ReportingPeriod($instant, $instant);
    }

    public function testRejectsReversedPeriod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ReportingPeriod(
            new DateTimeImmutable('2026-08-02T00:00:00Z'),
            new DateTimeImmutable('2026-08-01T00:00:00Z'),
        );
    }
}
