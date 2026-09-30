<?php

/**
 * SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

use Behat\Hook\BeforeSuite;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use GuzzleHttp\Client as HttpClient;
use LibreCode\UsageStatistics\Client;
use LibreCode\UsageStatistics\ConsentState;
use LibreCode\UsageStatistics\Endpoint;
use LibreCode\UsageStatistics\InstallationId;
use LibreCode\UsageStatistics\Metric;
use LibreCode\UsageStatistics\Report;
use LibreCode\UsageStatistics\ReportingPeriod;
use LibreCode\UsageStatistics\SubmissionResult;
use LibreCode\UsageStatistics\Transport\Response;
use LibreCode\UsageStatistics\Transport\TransportInterface;
use Libresign\NextcloudBehat\NextcloudApiContext;

final class FeatureContext extends NextcloudApiContext {
	#[BeforeSuite]
	public static function beforeSuite(BeforeSuiteScope $scope): void {
		parent::beforeSuite($scope);
		self::runCommand('config:system:set debug --value true --type boolean');
		self::runCommand('config:system:set auth.bruteforce.protection.enabled --value false --type boolean');
		self::runCommand('config:system:set ratelimit.protection.enabled --value false --type boolean');
		self::runCommand('app:enable --force usage_statistics_server');
	}

	#[Given('as anonymous user')]
	public function asAnonymousUser(): void {
		$this->setCurrentUser('');
	}

	#[When('the usage statistics client submits the compatibility report')]
	public function submitCompatibilityReport(): void {
		$report = new Report(
			application: 'client_server_e2e',
			installationId: (string)InstallationId::derive('client_server_e2e', 'integration-installation'),
			schemaVersion: 1,
			period: new ReportingPeriod(
				new \DateTimeImmutable('2026-09-01T00:00:00Z'),
				new \DateTimeImmutable('2026-10-01T00:00:00Z'),
			),
			metrics: [
				Metric::string('environment', 'version', '1.2.3'),
				Metric::integer('usage', 'requests_completed', 17),
			],
		);

		$transport = new class($this->baseUrl) implements TransportInterface {
			public function __construct(
				private readonly string $baseUrl,
			) {
			}

			public function request(
				string $method,
				string $url,
				array $headers,
				string $body,
				float $timeoutSeconds,
			): Response {
				$path = parse_url($url, PHP_URL_PATH);
				if (!is_string($path) || $path === '') {
					throw new \RuntimeException('Compatibility endpoint path is invalid.');
				}

				$http = new HttpClient(['http_errors' => false]);
				$response = $http->request(
					$method,
					rtrim($this->baseUrl, '/') . $path,
					[
						'headers' => $headers,
						'body' => $body,
						'timeout' => $timeoutSeconds,
					],
				);

				$responseHeaders = [];
				foreach ($response->getHeaders() as $name => $values) {
					$responseHeaders[$name] = implode(', ', $values);
				}

				return new Response(
					$response->getStatusCode(),
					(string)$response->getBody(),
					$responseHeaders,
				);
			}
		};

		$client = new Client(
			$transport,
			new Endpoint('https://usage-statistics.invalid/index.php/apps/usage_statistics_server/api/v1/reports'),
		);

		$result = $client->submit($report, ConsentState::Enabled);
		if ($result !== SubmissionResult::Accepted) {
			throw new \RuntimeException('Compatibility report was not accepted.');
		}
	}
}
