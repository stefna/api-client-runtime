<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\OpenApi\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Stefna\ApiClientRuntime\Exceptions\ApiClientResponseException;

final class UnknownResponse extends \RuntimeException implements ApiClientResponseException
{
	private ResponseInterface $response;

	public function __construct(ResponseInterface $response)
	{
		parent::__construct(sprintf('Unknown response (Status Code: %d)', $response->getStatusCode()));
		$this->response = $response;
	}

	public function getResponse(): ResponseInterface
	{
		return $this->response;
	}
}
