<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics;

enum ConsentState: string
{
    case Unknown = 'unknown';
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
