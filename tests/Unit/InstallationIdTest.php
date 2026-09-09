<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use InvalidArgumentException;
use LibreCode\UsageStatistics\InstallationId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InstallationIdTest extends TestCase {
	public function testDerivationIsStableAndApplicationScoped(): void {
		$first = (string)InstallationId::derive('libresign', 'local-instance-id');
		$second = (string)InstallationId::derive('libresign', 'local-instance-id');
		$otherApp = (string)InstallationId::derive('talk', 'local-instance-id');

		self::assertSame('dc8adebdce9ab99790a7d037f44965b4ca7b6dceb38d5e7c9dff118721fc8415', $first);
		self::assertSame($first, $second);
		self::assertNotSame($first, $otherApp);
		self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first);
		self::assertStringNotContainsString('local-instance-id', $first);
	}

	public function testAcceptsMaximumApplicationLength(): void {
		self::assertMatchesRegularExpression(
			'/^[a-f0-9]{64}$/',
			(string)InstallationId::derive(str_repeat('a', 128), 'local-instance-id'),
		);
	}

	#[DataProvider('invalidApplications')]
	public function testRejectsInvalidApplications(string $application): void {
		$this->expectException(InvalidArgumentException::class);
		InstallationId::derive($application, 'local-instance-id');
	}

	/** @return iterable<string,array{string}> */
	public static function invalidApplications(): iterable {
		yield 'empty' => [''];
		yield 'too long' => [str_repeat('a', 129)];
		yield 'invalid alphabet' => ['libresign app'];
	}

	public function testRejectsEmptyLocalInstallationIdentifier(): void {
		$this->expectException(InvalidArgumentException::class);
		InstallationId::derive('libresign', '');
	}
}
