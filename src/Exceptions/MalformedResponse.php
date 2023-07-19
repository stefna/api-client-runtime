<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Exceptions;

use Psr\Http\Message\ResponseInterface;

final class MalformedResponse extends \RuntimeException implements ApiClientResponseException
{
	private ResponseInterface $response;

	public function __construct(ResponseInterface $response)
	{
		parent::__construct('Failed to parse response');
		$this->response = $response;
	}

	public function getResponse(): ResponseInterface
	{
		return $this->response;
	}
}
