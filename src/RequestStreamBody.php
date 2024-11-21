<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

interface RequestStreamBody
{
	public function getContentType(): string;

	public function getRequestStreamBody(StreamFactoryInterface $streamFactory): StreamInterface;
}
