<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics;

use InvalidArgumentException;

final class InstallationId
{
    private const APPLICATION_PATTERN = '/^[A-Za-z0-9_.:-]+$/D';

    private function __construct(public readonly string $value)
    {
    }

    public static function derive(string $application, string $localInstallationIdentifier): self
    {
        if (
            $application === ''
            || strlen($application) > 128
            || preg_match(self::APPLICATION_PATTERN, $application) !== 1
        ) {
            throw new InvalidArgumentException('Application identifier is invalid.');
        }
        if ($localInstallationIdentifier === '') {
            throw new InvalidArgumentException('Local installation identifier must not be empty.');
        }

        $input = "usage-statistics:v1\0" . $application . "\0" . $localInstallationIdentifier;

        return new self(hash('sha256', $input));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
