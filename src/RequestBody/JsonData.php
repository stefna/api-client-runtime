<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\RequestBody;

use Starburst\Utils\Traits\GetArrayCopyTrait;
use Stefna\ApiClientRuntime\RequestBody;

final class JsonData implements RequestBody, \JsonSerializable
{
	use GetArrayCopyTrait;

	public function __construct(
		private mixed $data,
	) {}

	public function getBody(): string
	{
		return json_encode($this->data, JSON_THROW_ON_ERROR);
	}

	public function getContentType(): string
	{
		return 'application/json';
	}

	public function jsonSerialize(): mixed
	{
		return $this->data;
	}
}
