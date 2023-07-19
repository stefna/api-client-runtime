<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Endpoint;

use Stefna\ApiClientRuntime\Endpoint as EndpointContract;
use Stefna\ApiClientRuntime\RequestBody;
use Stefna\ApiClientRuntime\ServerConfiguration\SecurityScheme;

class Endpoint implements EndpointContract
{
	protected string $path;
	/** @var array<string, mixed> */
	protected array $query;
	/** @var array<string, string> */
	protected array $headers;
	/** @var list<string> */
	protected array $security;
	protected ?RequestBody $body;
	protected string $method;

	/**
	 * @param array<string, mixed> $query
	 * @param array<string, string> $headers
	 * @param list<string> $security
	 */
	public function __construct(
		string $method,
		string $path,
		RequestBody $body = null,
		array $query = [],
		array $headers = [],
		array $security = []
	) {
		$this->path = $path;
		$this->query = $query;
		$this->headers = $headers;
		$this->security = $security;
		$this->body = $body;
		$this->method = $method;
	}

	public function getHeaders(): array
	{
		return $this->headers;
	}

	public function getQueryParams(): array
	{
		return $this->query;
	}

	public function getRequestBody(): ?RequestBody
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
