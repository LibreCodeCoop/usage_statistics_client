<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests\Transport;

use LibreCode\UsageStatistics\Exception\TransportException;
use LibreCode\UsageStatistics\Transport\StreamTransport;
use PHPUnit\Framework\TestCase;

final class StreamTransportTest extends TestCase
{
    public function testRejectsNonPositiveTimeoutBeforeNetworkAccess(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Timeout must be greater than zero.');

        (new StreamTransport())->request('POST', 'https://stats.example/api/v1/reports', [], '{}', 0.0);
    }
}
