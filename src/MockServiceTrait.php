<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Stefna\HttpClient\HttpFactory;

trait MockServiceTrait
{
	public ResponseFactoryInterface $responseFactory;
	public StreamFactoryInterface $streamFactory;
	private ?\Closure $executeHandler = null;
	/** @var array<string, ResponseInterface> */
	private array $responseMap = [];

	public function setExecuteHandler(\Closure $handler)
	{
		$this->executeHandler = $handler;
	}

	public function addToResponseMap(string $path, ResponseInterface $response): self
	{
		$this->responseMap[$path] = $response;
		return $this;
	}

	public function addToResponseMapFromEndpoint(Endpoint $endpoint, ResponseInterface $response): self
	{
		return $this->addToResponseMap($endpoint->getPath(), $response);
	}

	public function createResponseFromArray(array $data): ResponseInterface
	{
		$this->needFactories();

		$stream = $this->streamFactory->createStream((string)json_encode($data));
		return $this->responseFactory->createResponse()->withBody($stream);
	}

	protected function executeRequest(RequestInterface $request): ResponseInterface
	{
		$this->needFactories();

		if ($this->executeHandler) {
			$handler = \Closure::bind($this->executeHandler, $this, $this);
			return $handler($request);
		}
		$pathKey = $request->getUri()->getPath();
		if (array_key_exists($pathKey, $this->responseMap)) {
			return $this->responseMap[$pathKey];
		}

		throw new class ('No handler or response found for path') extends \RuntimeException implements ClientExceptionInterface {};
	}

	private function needFactories(): void
	{
		if (!isset($this->responseFactory)) {
			$factory = new HttpFactory();
			$this->responseFactory = $factory;
			$this->streamFactory = $factory;
		}
	}
}
