<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class ReportingPeriod
{
    private const MAX_SECONDS = 2_678_400;

    public readonly DateTimeImmutable $start;
    public readonly DateTimeImmutable $end;

    public function __construct(DateTimeImmutable $start, DateTimeImmutable $end)
    {
        $utc = new DateTimeZone('UTC');
        $this->start = $start->setTimezone($utc);
        $this->end = $end->setTimezone($utc);

        $duration = $this->end->getTimestamp() - $this->start->getTimestamp();
        if ($duration <= 0 || $duration > self::MAX_SECONDS) {
            throw new InvalidArgumentException('Reporting period must be positive and no longer than 31 days.');
        }
    }

    public static function monthContaining(DateTimeImmutable $instant): self
    {
        $utc = $instant->setTimezone(new DateTimeZone('UTC'));
        $start = $utc->modify('first day of this month')->setTime(0, 0, 0, 0);
        $end = $start->modify('first day of next month');

        return new self($start, $end);
    }

    /** @return array{start:string,end:string} */
    public function toArray(): array
    {
        return [
            'start' => $this->start->format('Y-m-d\TH:i:s\Z'),
            'end' => $this->end->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
