<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Endpoint;

use Stefna\ApiClientRuntime\Endpoint as EndpointContract;
use Stefna\ApiClientRuntime\RequestBody;
use Stefna\ApiClientRuntime\RequestStreamBody;
use Stefna\ApiClientRuntime\ServerConfiguration\SecurityScheme;

class Endpoint implements EndpointContract
{
	/**
	 * @param array<string, mixed> $query
	 * @param array<string, string> $headers
	 * @param list<string> $security
	 */
	public function __construct(
		protected string $method,
		protected string $path,
		protected null|RequestBody|RequestStreamBody $body = null,
		protected array $query = [],
		protected array $headers = [],
		protected array $security = [],
	) {}

	public function getHeaders(): array
	{
		return $this->headers;
	}

	public function getQueryParams(): array
	{
		return $this->query;
	}

	public function getRequestBody(): null|RequestBody|RequestStreamBody
	{
		return $this->body;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	final public function getMethod(): string
	{
		return $this->method;
	}

	public function getDefaultSecurity(): string
	{
		return SecurityScheme::NO_SECURITY;
	}

	public function getSecurity(): array
	{
		return $this->security;
	}
}
