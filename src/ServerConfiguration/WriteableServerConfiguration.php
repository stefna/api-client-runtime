<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\ServerConfiguration;

use Stefna\ApiClientRuntime\ServerConfiguration;

interface WriteableServerConfiguration extends ServerConfiguration
{
	public function setSecurityValue(string $ref, SecurityValue $value): void;
}
