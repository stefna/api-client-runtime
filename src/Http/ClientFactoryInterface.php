<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Http;

use Psr\Http\Client\ClientInterface;

interface ClientFactoryInterface
{
	public function createClient(): ClientInterface;
}
