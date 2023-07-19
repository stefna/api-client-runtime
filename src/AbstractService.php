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

	protected ClientInterface $client;
	protected ServerConfiguration $serverConfiguration;
	private RequestFactoryInterface $requestFactory;
	private ?ResponseInterface $lastResponse = null;
	private ?RequestInterface $lastRequest = null;

	/**
	 * @return static
	 */
	public static function create(ServerConfiguration $serverConfiguration, HttpFactory $factory = null)
	{
		if (!$factory) {
			$factory = new HttpFactory();
		}
		$client = $factory->createClient();

		// @phpstan-ignore-next-line
		return new static($serverConfiguration, $client, $factory);
	}

	public function __construct(
		ServerConfiguration $serverConfiguration,
		ClientInterface $client,
		RequestFactoryInterface $requestFactory
	) {
		$this->serverConfiguration = $serverConfiguration;
		$this->client = $client;
		$this->requestFactory = $requestFactory;
	}

	public function getServerConfiguration(): ServerConfiguration
	{
		return $this->serverConfiguration;
	}

	/**
	 * @return static
	 */
	public function withServerConfiguration(ServerConfiguration $serverConfiguration)
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
			$body = $request->getBody();
			$body->rewind();
			$body->write($requestBody->getBody());
			$body->rewind();
			$request = $request->withHeader('Content-Type', $requestBody->getContentType());
			$request = $request->withBody($body);
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
	protected function parseJsonResponse(ResponseInterface $response)
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

	public function getLastResponse(): ?ResponseInterface
	{
		return $this->lastResponse;
	}

	public function getLastRequest(): ?RequestInterface
	{
		return $this->lastRequest;
	}
}
