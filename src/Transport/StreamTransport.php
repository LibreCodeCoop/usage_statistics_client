<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace LibreCode\UsageStatistics\Transport;

use LibreCode\UsageStatistics\Exception\TransportException;

final class StreamTransport implements TransportInterface {
    public function request(string $method, string $url, array $headers, string $body, float $timeoutSeconds): Response {
        if ($timeoutSeconds <= 0) {
            throw new TransportException('Timeout must be greater than zero.');
        }

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headerLines),
                'content' => $body,
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
                'follow_location' => 0,
            ],
        ]);

        $previous = set_error_handler(static fn (): bool => true);
        try {
            $responseBody = file_get_contents($url, false, $context);
        } finally {
            restore_error_handler();
        }

        /** @var list<string>|null $http_response_header */
        if ($responseBody === false || !isset($http_response_header)) {
            throw new TransportException('Unable to reach usage statistics server.');
        }

        $statusCode = null;
        $responseHeaders = [];
        foreach ($http_response_header as $line) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})\b/', $line, $matches) === 1) {
                $statusCode = (int)$matches[1];
                $responseHeaders = [];
                continue;
            }
            $separator = strpos($line, ':');
            if ($separator !== false) {
                $name = strtolower(trim(substr($line, 0, $separator)));
                $responseHeaders[$name] = trim(substr($line, $separator + 1));
            }
        }

        if ($statusCode === null) {
            throw new TransportException('Server response did not contain an HTTP status line.');
        }

        return new Response($statusCode, $responseBody, $responseHeaders);
    }
}
