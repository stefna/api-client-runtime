<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\OpenApi;

interface ModelResponseFactory
{
	public static function createFromResponse(mixed $data): static;
}
