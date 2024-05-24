<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\OpenApi;

use Stefna\ApiClientRuntime\OpenApi\Exceptions\UnknownSecuritySchema;
use Stefna\ApiClientRuntime\ServerConfiguration\ApiKeySecurityScheme;
use Stefna\ApiClientRuntime\ServerConfiguration\HttpSecurityScheme;
use Stefna\ApiClientRuntime\ServerConfiguration\SecurityScheme;

/**
 * @phpstan-type ApiKeySecurity array{type: 'apiKey', name: string, in: string}
 * @phpstan-type HttpSecurity array{type: 'http', scheme: string}
 * @phpstan-type OAuth2Security array{type: 'oauth2'}
 * @phpstan-type UnknownSecurity array{type: 'unknown'}
 */
final class SecuritySchemeFactory
{
	/**
	 * @param ApiKeySecurity|HttpSecurity|OAuth2Security|UnknownSecurity $scheme
	 */
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
