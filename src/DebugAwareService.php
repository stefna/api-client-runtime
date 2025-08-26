<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime;

interface DebugAwareService
{
	/**
	 * @return array<mixed>
	 */
	public function getRequestDebugInfo(
		bool $includeResponseBody = false,
		bool $includeRequestBody = false,
	): array;
}
