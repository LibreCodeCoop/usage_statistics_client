<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$scoped = $root . '/build/scoped';

spl_autoload_register(static function (string $class) use ($scoped): void {
    $prefix = 'UsageStatisticsClientScoped\\LibreCode\\UsageStatistics\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $scoped . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$state = UsageStatisticsClientScoped\LibreCode\UsageStatistics\ConsentState::Enabled;
if ($state->value !== 'enabled') {
    throw new RuntimeException('Scoped enum did not load correctly.');
}

$metric = UsageStatisticsClientScoped\LibreCode\UsageStatistics\Metric::integer('usage', 'count', 1);
if ($metric->toArray()['value'] !== 1) {
    throw new RuntimeException('Scoped value object did not execute correctly.');
}
