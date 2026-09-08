<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics;

use InvalidArgumentException;

final class Report
{
    public const PROTOCOL_VERSION = 1;
    private const IDENTIFIER_PATTERN = '/^[A-Za-z0-9_.:-]+$/D';
    private const MAX_METRICS = 256;

    /** @var list<Metric> */
    public readonly array $metrics;

    /** @param list<Metric> $metrics */
    public function __construct(
        public readonly string $application,
        public readonly string $installationId,
        public readonly int $schemaVersion,
        public readonly ReportingPeriod $period,
        array $metrics,
    ) {
        self::assertIdentifier($application, 'Application', 128);
        self::assertIdentifier($installationId, 'Installation ID', 128);
        if ($schemaVersion < 1) {
            throw new InvalidArgumentException('Schema version must be a positive integer.');
        }
        if ($metrics === [] || count($metrics) > self::MAX_METRICS) {
            throw new InvalidArgumentException('Report must contain between 1 and 256 metrics.');
        }

        $seen = [];
        foreach ($metrics as $metric) {
            $identity = $metric->identity();
            if (isset($seen[$identity])) {
                throw new InvalidArgumentException('Duplicate metric category/key pair.');
            }
            $seen[$identity] = true;
        }
        $this->metrics = $metrics;
    }

    /** @return array{protocolVersion:int,application:string,installationId:string,schemaVersion:int,period:array{start:string,end:string},metrics:list<array{category:string,key:string,type:string,value:string|int|float|bool}>} */
    public function toArray(): array
    {
        return [
            'protocolVersion' => self::PROTOCOL_VERSION,
            'application' => $this->application,
            'installationId' => $this->installationId,
            'schemaVersion' => $this->schemaVersion,
            'period' => $this->period->toArray(),
            'metrics' => array_map(static fn (Metric $metric): array => $metric->toArray(), $this->metrics),
        ];
    }

    private static function assertIdentifier(string $value, string $field, int $maxLength): void
    {
        if ($value === '' || strlen($value) > $maxLength || preg_match(self::IDENTIFIER_PATTERN, $value) !== 1) {
            throw new InvalidArgumentException($field . ' is invalid.');
        }
    }
}
