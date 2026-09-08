<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics;

enum SubmissionResult: string {
	case Accepted = 'accepted';
	case SkippedWithoutConsent = 'skipped_without_consent';
}
