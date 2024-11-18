<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Authentication;

use Psr\Http\Message\ResponseInterface;
use Stefna\ApiClientRuntime\AbstractService;
use Stefna\ApiClientRuntime\OpenApi\Exceptions\AccessForbidden;
use Stefna\ApiClientRuntime\OpenApi\Exceptions\NotAuthorized;
use Stefna\ApiClientRuntime\OpenApi\Exceptions\UnknownResponse;
use Stefna\ApiClientRuntime\ServerConfiguration;

/**
 * @property-read ServerConfiguration $serverConfiguration
 */
trait AuthenticationHelperTrait
{
	protected bool $authenticated = false;
	protected string $currentToken;

	protected function _setCurrentAuthentication(string $token): void
	{
		$this->authenticated = true;
		$this->currentToken = $token;
	}

	public function getCurrentAuthenticationValue(): mixed
	{
		return $this->currentToken;
	}

	protected function checkIfNeedAuthentication(): bool
	{
		if ($this instanceof AuthenticatedServerConfiguration && $this->needAuthentication()) {
			return true;
		}

		if (
			$this->serverConfiguration instanceof AuthenticatedServerConfiguration
			&& $this->serverConfiguration->needAuthentication()
		) {
			return true;
		}

		return !$this->authenticated;
	}

	/**
	 * @return array{0: string, 1: string}
	 */
	protected function findClientCredentials(): array
	{
		if ($this instanceof AuthenticatedServerConfiguration) {
			return [$this->getClientId(), $this->getClientSecret()];
		}

		if ($this->serverConfiguration instanceof AuthenticatedServerConfiguration) {
			return [$this->serverConfiguration->getClientId(), $this->serverConfiguration->getClientSecret()];
		}

		throw new \BadMethodCallException('Missing username and password');
	}

	/**
	 * @return array<mixed>
	 */
	protected function parseAuthResponse(ResponseInterface $response): array
	{
		$responseCode = $response->getStatusCode();
		if ($responseCode === 401) {
			throw new NotAuthorized(sprintf(
				'Unauthorized access for %s',
				$this->serverConfiguration->getBaseUri(),
			), $response);
		}
		if ($responseCode === 403) {
			throw new AccessForbidden('Forbidden', $response);
		}
		if ($response->getStatusCode() !== 200) {
			throw new UnknownResponse($response);
		}

		if (!$this instanceof AbstractService) {
			throw new \BadMethodCallException('Trait need to be part of AbstractService');
		}

		return $this->parseJsonResponse($response);
	}
}
