<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Http;

use Buzz\Client\Curl;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class HttpFactory implements
	RequestFactoryInterface,
	ResponseFactoryInterface,
	ServerRequestFactoryInterface,
	StreamFactoryInterface,
	UploadedFileFactoryInterface,
	UriFactoryInterface,
	ClientFactoryInterface
{
	private Psr17Factory $factory;

	final public function __construct()
	{
		$this->factory = new Psr17Factory();
	}

	public function createRequest(string $method, $uri): RequestInterface
	{
		return $this->factory->createRequest($method, $uri);
	}

	public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
	{
		return $this->factory->createResponse($code, $reasonPhrase);
	}

	/**
	 * @param array<string, scalar> $serverParams
	 */
	public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
	{
		return $this->factory->createServerRequest($method, $uri, $serverParams);
	}

	public function createStream(string $content = ''): StreamInterface
	{
		return $this->factory->createStream($content);
	}

	public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
	{
		return $this->factory->createStreamFromFile($filename, $mode);
	}

	public function createStreamFromResource($resource): StreamInterface
	{
		return $this->factory->createStreamFromResource($resource);
	}

	public function createUploadedFile(
		StreamInterface $stream,
		int $size = null,
		int $error = \UPLOAD_ERR_OK,
		string $clientFilename = null,
		string $clientMediaType = null
	): UploadedFileInterface {
		return $this->factory->createUploadedFile($stream, $size, $error, $clientFilename, $clientFilename);
	}

	public function createUri(string $uri = ''): UriInterface
	{
		return $this->factory->createUri($uri);
	}

	public function createClient(): ClientInterface
	{
		return new Curl($this->factory);
	}
}
