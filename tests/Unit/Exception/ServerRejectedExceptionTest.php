<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests\Exception;

use LibreCode\UsageStatistics\Exception\ServerRejectedException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServerRejectedExceptionTest extends TestCase {
	public function testPreservesServerMessage(): void {
		$exception = new ServerRejectedException(400, 'invalid_report', 'Invalid report.');

		self::assertSame('Invalid report.', $exception->getMessage());
		self::assertSame('invalid_report', $exception->errorCode);
	}

	#[DataProvider('transientStatuses')]
	public function testTransientStatusClassification(int $statusCode, bool $expected): void {
		self::assertSame($expected, (new ServerRejectedException($statusCode))->isTransient());
	}

	/** @return iterable<string,array{int,bool}> */
	public static function transientStatuses(): iterable {
		yield 'validation' => [400, false];
		yield 'conflict' => [409, false];
		yield 'rate limited' => [429, true];
		yield 'server error boundary' => [500, true];
		yield 'gateway error' => [502, true];
	}
}
