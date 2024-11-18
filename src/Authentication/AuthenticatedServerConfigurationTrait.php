<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Authentication;

trait AuthenticatedServerConfigurationTrait
{
	protected bool $needAuthenticated = true;
	protected string $currentClientId;
	protected string $currentClientSecret;

	public function setClientCredentials(
		string $clientId,
		#[\SensitiveParameter]
		string $clientSecret,
	): void {
		if (isset($this->currentClientId) && $clientId !== $this->currentClientId) {
			$this->needAuthenticated = true;
		}
		$this->currentClientId = $clientId;
		$this->currentClientSecret = $clientSecret;
	}

	public function needAuthentication(): bool
	{
		return $this->needAuthenticated;
	}

	public function getClientId(): string
	{
		return $this->currentClientId ?? '';
	}

	public function getClientSecret(): string
	{
		return $this->currentClientSecret ?? '';
	}
}
