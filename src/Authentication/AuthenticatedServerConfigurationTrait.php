<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Authentication;

trait AuthenticatedServerConfigurationTrait
{
	protected bool $needsAuthentication = true;
	protected string $currentClientId;
	protected string $currentClientSecret;

	public function setClientCredentials(
		string $clientId,
		#[\SensitiveParameter]
		string $clientSecret,
	): void {
		if (isset($this->currentClientId) && $clientId !== $this->currentClientId) {
			$this->needsAuthentication = true;
		}
		$this->currentClientId = $clientId;
		$this->currentClientSecret = $clientSecret;
	}

	public function needsAuthentication(): bool
	{
		return $this->needsAuthentication;
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
