<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics;

use InvalidArgumentException;

final class Metric
{
    private const IDENTIFIER_PATTERN = '/^[A-Za-z0-9_.:-]+$/D';
    private const MAX_CATEGORY_LENGTH = 128;
    private const MAX_KEY_LENGTH = 512;
    private const MAX_STRING_VALUE_LENGTH = 1024;

    private function __construct(
        public readonly string $category,
        public readonly string $key,
        public readonly string $type,
        public readonly string|int|float|bool $value,
    ) {
        self::assertIdentifier($category, 'Metric category', self::MAX_CATEGORY_LENGTH);
        self::assertIdentifier($key, 'Metric key', self::MAX_KEY_LENGTH);
    }

    public static function integer(string $category, string $key, int $value): self
    {
        return new self($category, $key, 'integer', $value);
    }

    public static function number(string $category, string $key, int|float $value): self
    {
        if (is_float($value) && !is_finite($value)) {
            throw new InvalidArgumentException('Number metric must be finite.');
        }

        return new self($category, $key, 'number', $value);
    }

    public static function boolean(string $category, string $key, bool $value): self
    {
        return new self($category, $key, 'boolean', $value);
    }

    public static function string(string $category, string $key, string $value): self
    {
        if (strlen($value) > self::MAX_STRING_VALUE_LENGTH) {
            throw new InvalidArgumentException('String metric value exceeds 1024 bytes.');
        }

        return new self($category, $key, 'string', $value);
    }

    /** @return array{category:string,key:string,type:string,value:string|int|float|bool} */
    public function toArray(): array
    {
        return [
            'category' => $this->category,
            'key' => $this->key,
            'type' => $this->type,
            'value' => $this->value,
        ];
    }

    public function identity(): string
    {
        return $this->category . "\0" . $this->key;
    }

    private static function assertIdentifier(string $value, string $field, int $maxLength): void
    {
        if ($value === '' || strlen($value) > $maxLength || preg_match(self::IDENTIFIER_PATTERN, $value) !== 1) {
            throw new InvalidArgumentException($field . ' is invalid.');
        }
    }
}
