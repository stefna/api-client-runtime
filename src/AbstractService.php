<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Starburst\Utils\Json;
use Stefna\ApiClientRuntime\Authentication\GeneralAuthenticatedService;
use Stefna\ApiClientRuntime\Exceptions\MalformedResponse;
use Stefna\ApiClientRuntime\Exceptions\RequestFailed;
use Stefna\ApiClientRuntime\Http\ClientFactoryInterface;
use Stefna\ApiClientRuntime\Http\HttpFactory;

abstract class AbstractService implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	private ?ResponseInterface $lastResponse = null;
	private ?RequestInterface $lastRequest = null;
	private ?Endpoint $lastEndpoint = null;
	private bool $doingAuthentication = false;

	public static function create(
		ServerConfiguration $serverConfiguration,
		ClientInterface|ClientFactoryInterface $clientFactory = null,
		RequestFactoryInterface $requestFactory = null,
		StreamFactoryInterface $streamFactory = null,
	): static {
		$client = $clientFactory instanceof ClientInterface ? $clientFactory : null;
		if (!$client || !$requestFactory || !$streamFactory) {
			$factory = new HttpFactory();
			$client ??= $factory->createClient();
			$requestFactory ??= $factory;
			$streamFactory ??= $factory;
		}

		// @phpstan-ignore new.static
		return new static($serverConfiguration, $client, $requestFactory, $streamFactory);
	}

	public function __construct(
		protected ServerConfiguration $serverConfiguration,
		protected ClientInterface $client,
		private RequestFactoryInterface $requestFactory,
		private null|StreamFactoryInterface $streamFactory = null,
	) {}

	public function getServerConfiguration(): ServerConfiguration
	{
		return $this->serverConfiguration;
	}

	public function withServerConfiguration(ServerConfiguration $serverConfiguration): static
	{
		$self = clone $this;
		$self->serverConfiguration = $serverConfiguration;
		return $self;
	}

	protected function doRequest(Endpoint $endpoint): ResponseInterface
	{
		$this->lastEndpoint = $endpoint;
		$this->lastResponse = null;
		if ($this instanceof GeneralAuthenticatedService && !$this->doingAuthentication) {
			$this->doingAuthentication = true;
			$this->executeAuthentication();
			$this->doingAuthentication = false;
		}

		$endpointUri = $this->serverConfiguration->getBaseUri() . $endpoint->getPath();
		$request = $this->requestFactory->createRequest($endpoint->getMethod(), $endpointUri);

		$uri = $request->getUri();

		$queryParams = $endpoint->getQueryParams();
		$headerParams = $endpoint->getHeaders();
		$requestBody = $endpoint->getRequestBody();
		if ($queryParams) {
			$uri = $uri->withQuery(http_build_query($queryParams));
		}
		if ($headerParams) {
			foreach ($headerParams as $key => $value) {
				$request = $request->withHeader($key, $value);
			}
		}
		if ($requestBody) {
			$request = $this->buildRequestBody($request, $requestBody);
		}
		$request = $request->withUri($uri);

		$request = $this->serverConfiguration->configureAuthentication($request, $endpoint);

		try {
			$this->lastRequest = $request;
			$response = $this->executeRequest($request);
			$this->lastResponse = $response;
			return $response;
		}
		catch (ClientExceptionInterface $e) {
			$this->logger && $this->logger->info('Error talking to api', [
				'exception' => $e,
			]);
		}

		throw new RequestFailed('Failed to query api', 0, $e);
	}

	protected function executeRequest(RequestInterface $request): ResponseInterface
	{
		return $this->client->sendRequest($request);
	}

	/**
	 * @return array|mixed
	 */
	protected function parseJsonResponse(ResponseInterface $response): mixed
	{
		$body = (string)$response->getBody();
		if (!$body) {
			return [];
		}
		$json = json_decode($body, true);
		if ($json === null) {
			throw new MalformedResponse($response);
		}
		return $json;
	}

	protected function buildRequestBody(RequestInterface $request, RequestBody|RequestStreamBody $requestBody): RequestInterface
	{
		if ($requestBody instanceof RequestStreamBody) {
			if (!$this->streamFactory) {
				throw new \BadMethodCallException('Stream factory is required for RequestStreamBody');
			}
			return $request
				->withBody($requestBody->getRequestStreamBody($this->streamFactory))
				->withHeader('Content-Type', $requestBody->getContentType());
		}

		$body = $request->getBody();
		$body->rewind();
		$body->write($requestBody->getRequestBody());
		$body->rewind();
		$request = $request->withHeader('Content-Type', $requestBody->getContentType());
		return $request->withBody($body);
	}

	public function getLastResponse(): ?ResponseInterface
	{
		return $this->lastResponse;
	}

	public function getLastRequest(): ?RequestInterface
	{
		return $this->lastRequest;
	}

	/**
	 * @return array{
	 *     response?: array{
	 *         status: int|null,
	 *         body: string|null,
	 *         headers: array<mixed>|null,
	 *     },
	 *     request?: array{
	 *         host: string|null,
	 *         path: string|null,
	 *         body?: string|null,
	 *         query?: string|null,
	 *         headers: array<mixed>|null,
	 *     },
	 *     endpoint?: array{
	 *         class: string,
	 *         path: string,
	 *         method: string,
	 *         headers: array<string, string>,
	 *         queryParams: array<string, mixed>,
	 *         defaultSecurity: string,
	 *         security: list<string>,
	 *         body: mixed,
	 *     },
	 * }
	 */
	public function getRequestDebugInfo(
		bool $includeResponseBody = false,
		bool $includeRequestBody = false,
	): array {
		$debugInfo = [];
		if ($this->lastEndpoint) {
			$body = null;
			$requestBody = $this->lastEndpoint->getRequestBody();
			if ($requestBody && $includeRequestBody) {
				if ($requestBody instanceof RequestStreamBody) {
					$body = $requestBody->getContentType();
				}
			}
			$debugInfo['endpoint'] = [
				'class' => $this->lastEndpoint::class,
				'path' => $this->lastEndpoint->getPath(),
				'method' => $this->lastEndpoint->getMethod(),
				'headers' => $this->lastEndpoint->getHeaders(),
				'queryParams' => $this->lastEndpoint->getQueryParams(),
				'defaultSecurity' => $this->lastEndpoint->getDefaultSecurity(),
				'security' => $this->lastEndpoint->getSecurity(),
				'body' => $body,
			];
		}
		if ($this->lastRequest) {
			$debugInfo['request'] = [
				'host' => $this->lastRequest->getUri()->getHost(),
				'path' => $this->lastRequest->getUri()->getPath(),
				'body' => $includeRequestBody ? $this->lastRequest->getBody()->__toString() : null,
				'query' => $this->lastRequest->getUri()->getQuery(),
				'headers' => $this->lastRequest->getHeaders(),
			];
		}
		if ($this->lastResponse) {
			$debugInfo['response'] = [
				'body' => $includeResponseBody ? $this->lastResponse->getBody()->__toString() : null,
				'status' => $this->lastResponse->getStatusCode(),
				'headers' => $this->lastResponse->getHeaders(),
			];
		}
		return $debugInfo;
	}
}
