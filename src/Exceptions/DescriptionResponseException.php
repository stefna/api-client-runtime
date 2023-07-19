<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Exceptions;

use Psr\Http\Message\ResponseInterface;

class DescriptionResponseException extends \RuntimeException implements ApiClientResponseException
{
	private ResponseInterface $response;

	public function __construct(string $message, ResponseInterface $response)
	{
		parent::__construct($message);
		$this->response = $response;
	}

	public function getResponse(): ResponseInterface
	{
		return $this->response;
	}
}
