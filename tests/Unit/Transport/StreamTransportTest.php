<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use LibreCode\UsageStatistics\Exception\TransportException;
use LibreCode\UsageStatistics\Transport\StreamTransport;
use PHPUnit\Framework\TestCase;

final class StreamTransportTest extends TestCase
{
    public function testRejectsNonPositiveTimeout(): void
    {
        $transport = new StreamTransport();

        $this->expectException(TransportException::class);
        $transport->request('POST', 'https://example.invalid', [], '{}', 0.0);
    }
}
