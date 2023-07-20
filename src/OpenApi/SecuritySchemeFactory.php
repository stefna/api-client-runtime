<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\OpenApi;

use Stefna\ApiClientRuntime\OpenApi\Exceptions\UnknownSecuritySchema;
use Stefna\ApiClientRuntime\ServerConfiguration\ApiKeySecurityScheme;
use Stefna\ApiClientRuntime\ServerConfiguration\HttpSecurityScheme;
use Stefna\ApiClientRuntime\ServerConfiguration\SecurityScheme;

final class SecuritySchemeFactory
{
	public static function createFromSchemeArray(string $name, array $scheme): SecurityScheme
	{
		if ($scheme['type'] === 'apiKey') {
			return new ApiKeySecurityScheme($name, $scheme['name'], $scheme['in']);
		}
		if ($scheme['type'] === 'http') {
			return new HttpSecurityScheme($name, $scheme['type'], $scheme['scheme']);
		}
		if ($scheme['type'] === 'oauth2') {
			return new HttpSecurityScheme($name, 'http', 'bearer');
		}
		throw new UnknownSecuritySchema($name);
	}
}
