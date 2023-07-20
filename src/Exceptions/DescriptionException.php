<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Stefna\ApiClientRuntime\Exceptions\ApiClientResponseException;

class DescriptionException extends \RuntimeException implements ApiClientResponseException
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
