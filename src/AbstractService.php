<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Stefna\HttpClient\HttpFactory;
use Stefna\ApiClientRuntime\Exceptions\MalformedResponse;
use Stefna\ApiClientRuntime\Exceptions\RequestFailed;

abstract class AbstractService implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	private ?ResponseInterface $lastResponse = null;
	private ?RequestInterface $lastRequest = null;

	public static function create(ServerConfiguration $serverConfiguration, HttpFactory $factory = null): static
	{
		if (!$factory) {
			$factory = new HttpFactory();
		}
		$client = $factory->createClient();

		// @phpstan-ignore-next-line
		return new static($serverConfiguration, $client, $factory);
	}

	public function __construct(
		protected ServerConfiguration $serverConfiguration,
		protected ClientInterface $client,
		private RequestFactoryInterface $requestFactory,
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

	protected function buildRequestBody(RequestInterface $request, RequestBody $requestBody): RequestInterface
	{
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
}
