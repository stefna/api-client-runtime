<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Exceptions;

use Psr\Http\Message\ResponseInterface;

interface ApiClientResponseException extends ApiClientException
{
	public function getResponse(): ResponseInterface;
}
