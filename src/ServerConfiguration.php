<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime;

use Psr\Http\Message\RequestInterface;

interface ServerConfiguration
{
	public function getBaseUri(): string;

	public function configureAuthentication(RequestInterface $request, Endpoint $endpoint): RequestInterface;
}
