<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

$report = $argv[1] ?? 'build/coverage.xml';
$minimum = isset($argv[2]) ? (float)$argv[2] : 75.0;

$xml = simplexml_load_file($report);
if ($xml === false) {
    fwrite(STDERR, "Unable to read coverage report.\n");
    exit(2);
}

$metrics = $xml->project->metrics;
$statements = (int)$metrics['statements'];
$covered = (int)$metrics['coveredstatements'];
$percentage = $statements === 0 ? 0.0 : ($covered / $statements) * 100;

printf("Line coverage: %.2f%% (minimum %.2f%%)\n", $percentage, $minimum);
exit($percentage >= $minimum ? 0 : 1);
