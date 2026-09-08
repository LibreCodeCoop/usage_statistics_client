<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use InvalidArgumentException;
use LibreCode\UsageStatistics\Metric;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MetricTest extends TestCase
{
    public function testSerializesSupportedTypes(): void
    {
        self::assertSame(['category' => 'usage', 'key' => 'count', 'type' => 'integer', 'value' => 3], Metric::integer('usage', 'count', 3)->toArray());
        self::assertSame('number', Metric::number('usage', 'ratio', 1.5)->type);
        self::assertSame('boolean', Metric::boolean('feature', 'enabled', true)->type);
        self::assertSame('string', Metric::string('environment', 'version', '12.0.0')->type);
    }

    #[DataProvider('invalidIdentifiers')]
    public function testRejectsInvalidIdentifiers(string $category, string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        Metric::integer($category, $key, 1);
    }

    /** @return iterable<string,array{string,string}> */
    public static function invalidIdentifiers(): iterable
    {
        yield 'empty category' => ['', 'key'];
        yield 'spaces' => ['usage data', 'key'];
        yield 'empty key' => ['usage', ''];
    }

    public function testRejectsInfiniteNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Metric::number('usage', 'ratio', INF);
    }

    public function testRejectsOversizedString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Metric::string('usage', 'value', str_repeat('x', 1025));
    }
}
