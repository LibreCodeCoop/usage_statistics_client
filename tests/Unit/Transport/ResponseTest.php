<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use LibreCode\UsageStatistics\Transport\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testHeaderLookupIsCaseInsensitive(): void
    {
        $response = new Response(429, '', ['Retry-After' => '60']);

        self::assertSame('60', $response->header('retry-after'));
        self::assertSame('60', $response->header('RETRY-AFTER'));
    }
}
