<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use LibreCode\UsageStatistics\InstallationId;
use PHPUnit\Framework\TestCase;

final class InstallationIdTest extends TestCase
{
    public function testDerivationIsStableAndApplicationScoped(): void
    {
        $first = (string)InstallationId::derive('libresign', 'local-instance-id');
        $second = (string)InstallationId::derive('libresign', 'local-instance-id');
        $otherApp = (string)InstallationId::derive('talk', 'local-instance-id');

        self::assertSame($first, $second);
        self::assertNotSame($first, $otherApp);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first);
        self::assertStringNotContainsString('local-instance-id', $first);
    }
}
