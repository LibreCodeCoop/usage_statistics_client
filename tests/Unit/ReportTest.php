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
use PHPUnit\Framework\TestCase;

final class ReportTest extends TestCase
{
    public function testSerializesProtocolV1Payload(): void
    {
        $report = new Report(
            'libresign',
            str_repeat('a', 64),
            2,
            new ReportingPeriod(new DateTimeImmutable('2026-08-01T00:00:00Z'), new DateTimeImmutable('2026-09-01T00:00:00Z')),
            [Metric::string('environment', 'version', '12.0.0')],
        );

        self::assertSame(1, $report->toArray()['protocolVersion']);
        self::assertSame('2026-08-01T00:00:00Z', $report->toArray()['period']['start']);
        self::assertSame('12.0.0', $report->toArray()['metrics'][0]['value']);
    }

    public function testRejectsDuplicateMetrics(): void
    {
        $period = new ReportingPeriod(new DateTimeImmutable('2026-08-01T00:00:00Z'), new DateTimeImmutable('2026-08-02T00:00:00Z'));
        $this->expectException(InvalidArgumentException::class);
        new Report('libresign', str_repeat('a', 64), 1, $period, [
            Metric::integer('usage', 'count', 1),
            Metric::integer('usage', 'count', 2),
        ]);
    }

    public function testRejectsEmptyMetrics(): void
    {
        $period = new ReportingPeriod(new DateTimeImmutable('2026-08-01T00:00:00Z'), new DateTimeImmutable('2026-08-02T00:00:00Z'));
        $this->expectException(InvalidArgumentException::class);
        new Report('libresign', str_repeat('a', 64), 1, $period, []);
    }

    public function testRejectsInvalidSchemaVersion(): void
    {
        $period = new ReportingPeriod(new DateTimeImmutable('2026-08-01T00:00:00Z'), new DateTimeImmutable('2026-08-02T00:00:00Z'));
        $this->expectException(InvalidArgumentException::class);
        new Report('libresign', str_repeat('a', 64), 0, $period, [Metric::integer('usage', 'count', 1)]);
    }
}
