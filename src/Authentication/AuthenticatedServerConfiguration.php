<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Authentication;

interface AuthenticatedServerConfiguration
{
	public function setClientCredentials(
		string $clientId,
		#[\SensitiveParameter]
		string $clientSecret,
	): void;

	/**
	 * @return bool Returns true if credentials need to be authenticated
	 */
	public function needAuthentication(): bool;

	public function getClientId(): string;

	public function getClientSecret(): string;
}
