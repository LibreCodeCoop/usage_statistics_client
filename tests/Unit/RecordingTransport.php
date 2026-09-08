<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Tests;

use LibreCode\UsageStatistics\Transport\Response;
use LibreCode\UsageStatistics\Transport\TransportInterface;

final class RecordingTransport implements TransportInterface
{
    public int $calls = 0;
    public string $method = '';
    public string $url = '';
    /** @var array<string,string> */
    public array $headers = [];
    public string $body = '';
    public float $timeout = 0.0;

    public function __construct(private readonly Response $response)
    {
    }

    /** @param array<string,string> $headers */
    public function request(
        string $method,
        string $url,
        array $headers,
        string $body,
        float $timeoutSeconds,
    ): Response {
        ++$this->calls;
        $this->method = $method;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        $this->timeout = $timeoutSeconds;

        return $this->response;
    }
}
