<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\ServerConfiguration;

use Psr\Http\Message\RequestInterface;

final class ApiKeySecurityScheme implements SecurityScheme
{
	public function __construct(
		private string $ref,
		private string $name,
		private string $in,
	) {}

	public function getRef(): string
	{
		return $this->ref;
	}

	public function getType(): string
	{
		return 'apiKey';
	}

	public function configure(RequestInterface $request, ?SecurityValue $securityValue): RequestInterface
	{
		if ($this->in === 'header' && $securityValue) {
			return $request->withHeader($this->name, $securityValue->toString());
		}
		if ($this->in === 'query' && $securityValue) {
			$uri = $request->getUri();
			$query = $uri->getQuery();
			if (!$query) {
				$query = http_build_query([$this->name => $securityValue->toString()]);
			}
			else {
				$query .= '&' . $this->name . '=' . urlencode($securityValue->toString());
			}
			return $request->withUri($uri->withQuery($query));
		}

		return $request;
	}
}
