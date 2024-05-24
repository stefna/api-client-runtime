<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime;

interface RequestBody
{
	/**
	 * @return array<mixed>
	 */
	public function getArrayCopy(): array;

	public function getContentType(): string;

	public function getRequestBody(): string;
}
