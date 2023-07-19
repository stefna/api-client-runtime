<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\RequestBody;

use Stefna\ApiClientRuntime\RequestBody;

final class PostData implements RequestBody
{
	/** @var array<array-key, mixed> */
	private array $data;

	/**
	 * @param array<array-key, mixed> $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function getBody(): string
	{
		return http_build_query($this->data);
	}

	public function getContentType(): string
	{
		return 'application/x-www-form-urlencoded';
	}
}
