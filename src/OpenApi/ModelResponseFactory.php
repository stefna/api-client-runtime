<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\OpenApi;

interface ModelResponseFactory
{
	/**
	 * @param mixed $data
	 * @return static
	 */
	public static function createFromResponse($data);
}
