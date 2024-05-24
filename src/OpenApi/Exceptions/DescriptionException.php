<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\OpenApi\Exceptions;

use Psr\Http\Message\ResponseInterface;

class DescriptionException extends \Stefna\ApiClientRuntime\Exceptions\DescriptionException
{
	private ?object $model = null;

	public static function withModel(object $model, string $message, ResponseInterface $response): self
	{
		// @phpstan-ignore new.static
		$exception = new static($message, $response);
		$exception->model = $model;
		return $exception;
	}

	public function getModel(): ?object
	{
		return $this->model;
	}
}
