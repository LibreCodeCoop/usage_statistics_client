<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$scoped = $root . '/build/scoped';

spl_autoload_register(static function (string $class) use ($scoped): void {
	$prefix = 'OCA\\Libresign\\Vendor\\LibreCode\\UsageStatistics\\';
	if (!str_starts_with($class, $prefix)) {
		return;
	}
	$relative = substr($class, strlen($prefix));
	$file = $scoped . '/' . str_replace('\\', '/', $relative) . '.php';
	if (is_file($file)) {
		require $file;
	}
});

$state = OCA\Libresign\Vendor\LibreCode\UsageStatistics\ConsentState::Enabled;
if ($state->value !== 'enabled') {
	throw new RuntimeException('Scoped enum did not load correctly.');
}

$metric = OCA\Libresign\Vendor\LibreCode\UsageStatistics\Metric::integer('usage', 'count', 1);
if ($metric->toArray() !== ['category' => 'usage', 'key' => 'count', 'type' => 'integer', 'value' => 1]) {
	throw new RuntimeException('Scoped metric did not execute correctly.');
}

$endpoint = new OCA\Libresign\Vendor\LibreCode\UsageStatistics\Endpoint('https://stats.example/api/v1/reports');
if ((string)$endpoint !== 'https://stats.example/api/v1/reports') {
	throw new RuntimeException('Scoped endpoint did not execute correctly.');
}

$transport = new class implements OCA\Libresign\Vendor\LibreCode\UsageStatistics\Transport\TransportInterface {
	public function request(string $method, string $url, array $headers, string $body, float $timeoutSeconds): OCA\Libresign\Vendor\LibreCode\UsageStatistics\Transport\Response {
		return new OCA\Libresign\Vendor\LibreCode\UsageStatistics\Transport\Response(200, '{"status":"accepted"}');
	}
};

$client = new OCA\Libresign\Vendor\LibreCode\UsageStatistics\Client($transport, $endpoint);
if (!$client instanceof OCA\Libresign\Vendor\LibreCode\UsageStatistics\Client) {
	throw new RuntimeException('Scoped client did not instantiate correctly.');
}
