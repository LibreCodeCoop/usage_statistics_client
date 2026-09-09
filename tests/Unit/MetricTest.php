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

final class MetricTest extends TestCase {
	public function testSerializesSupportedTypes(): void {
		self::assertSame([
			'category' => 'usage',
			'key' => 'count',
			'type' => 'integer',
			'value' => 3,
		], Metric::integer('usage', 'count', 3)->toArray());
		self::assertSame('number', Metric::number('usage', 'ratio', 1.5)->type);
		self::assertSame('boolean', Metric::boolean('feature', 'enabled', true)->type);
		self::assertSame('string', Metric::string('environment', 'version', '12.0.0')->type);
	}

	#[DataProvider('invalidIdentifiers')]
	public function testRejectsInvalidIdentifiers(string $category, string $key, string $message): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage($message);
		Metric::integer($category, $key, 1);
	}

	/** @return iterable<string,array{string,string,string}> */
	public static function invalidIdentifiers(): iterable {
		yield 'empty category' => ['', 'key', 'Metric category is invalid.'];
		yield 'spaces' => ['usage data', 'key', 'Metric category is invalid.'];
		yield 'empty key' => ['usage', '', 'Metric key is invalid.'];
		yield 'category too long' => [str_repeat('a', 129), 'key', 'Metric category is invalid.'];
		yield 'key too long' => ['usage', str_repeat('a', 513), 'Metric key is invalid.'];
	}

	public function testAcceptsIdentifierAndStringBoundaries(): void {
		$metric = Metric::string(str_repeat('a', 128), str_repeat('b', 512), str_repeat('x', 1024));

		self::assertSame(128, strlen($metric->category));
		self::assertSame(512, strlen($metric->key));
		self::assertSame(1024, strlen((string)$metric->value));
	}

	#[DataProvider('invalidNumbers')]
	public function testRejectsNonFiniteNumber(float $value): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Number metric must be finite.');
		Metric::number('usage', 'ratio', $value);
	}

	/** @return iterable<string,array{float}> */
	public static function invalidNumbers(): iterable {
		yield 'positive infinity' => [INF];
		yield 'negative infinity' => [-INF];
		yield 'not a number' => [NAN];
	}

	public function testRejectsOversizedString(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('String metric value exceeds 1024 bytes.');
		Metric::string('usage', 'value', str_repeat('x', 1025));
	}

	public function testIdentityIsUnambiguousCategoryKeyPair(): void {
		self::assertSame("usage\0count", Metric::integer('usage', 'count', 1)->identity());
		self::assertNotSame(
			Metric::integer('first', 'same', 1)->identity(),
			Metric::integer('second', 'same', 1)->identity(),
		);
		self::assertNotSame(
			Metric::integer('same', 'first', 1)->identity(),
			Metric::integer('same', 'second', 1)->identity(),
		);
	}
}
