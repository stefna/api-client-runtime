<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\RequestBody;

use Starburst\Utils\Json;
use Starburst\Utils\Traits\GetArrayCopyTrait;
use Stefna\ApiClientRuntime\RequestBody;

final class JsonData implements RequestBody, \JsonSerializable
{
	use GetArrayCopyTrait;

	public function __construct(
		private mixed $data,
	) {}

	public function getRequestBody(): string
	{
		return Json::encode($this->data);
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
