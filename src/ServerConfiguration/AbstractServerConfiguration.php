<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\ServerConfiguration;

use Psr\Http\Message\RequestInterface;
use Stefna\ApiClientRuntime\Endpoint;
use Stefna\ApiClientRuntime\ServerConfiguration;

abstract class AbstractServerConfiguration implements ServerConfiguration
{
	abstract protected function getSecurityValue(string $ref): ?SecurityValue;

	abstract protected function getSecurityScheme(string $ref): ?SecurityScheme;

	public function configureAuthentication(RequestInterface $request, Endpoint $endpoint): RequestInterface
	{
		if (!count($endpoint->getSecurity())) {
			return $this->configureSecuritySchema($request, $endpoint->getDefaultSecurity());
		}
		foreach ($endpoint->getSecurity() as $schema) {
			$request = $this->configureSecuritySchema($request, $schema);
		}
		return $request;
	}

	private function configureSecuritySchema(RequestInterface $request, string $securitySchemaRef): RequestInterface
	{
		if ($securitySchemaRef === SecurityScheme::NO_SECURITY) {
			return $request;
		}
		$security = $this->getSecurityScheme($securitySchemaRef);
		if (!$security) {
			return $request;
		}
		$value = $this->getSecurityValue($securitySchemaRef);
		return $security->configure($request, $value);
	}
}
