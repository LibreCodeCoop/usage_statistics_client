<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use InvalidArgumentException;
use LibreCode\UsageStatistics\Endpoint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EndpointTest extends TestCase
{
    public function testAcceptsHttpsEndpoint(): void
    {
        self::assertSame('https://stats.example/api/v1/reports', (string)new Endpoint('https://stats.example/api/v1/reports/'));
    }

    #[DataProvider('invalidEndpoints')]
    public function testRejectsUnsafeEndpoints(string $endpoint): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Endpoint($endpoint);
    }

    /** @return iterable<string,array{string}> */
    public static function invalidEndpoints(): iterable
    {
        yield 'http' => ['http://stats.example/api/v1/reports'];
        yield 'credentials' => ['https://user:secret@stats.example/api/v1/reports'];
        yield 'query' => ['https://stats.example/api/v1/reports?token=x'];
    }
}
