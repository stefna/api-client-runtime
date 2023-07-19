<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime;

interface RequestBody
{
	public function getContentType(): string;

	public function getBody(): string;
}
