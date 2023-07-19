<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\RequestBody;

use Stefna\ApiClientRuntime\RequestBody;

final class JsonData implements RequestBody
{
	/** @var mixed */
	private $data;

	/**
	 * @param mixed $data
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	public function getBody(): string
	{
		return json_encode($this->data, JSON_THROW_ON_ERROR);
	}

	public function getContentType(): string
	{
		return 'application/json';
	}
}
