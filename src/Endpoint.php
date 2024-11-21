<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime;

interface Endpoint
{
	/**
	 * @return array<string, string>
	 */
	public function getHeaders(): array;

	/**
	 * @return array<string, mixed>
	 */
	public function getQueryParams(): array;
	public function getRequestBody(): null|RequestBody|RequestStreamBody;
	public function getPath(): string;
	public function getMethod(): string;
	public function getDefaultSecurity(): string;

	/**
	 * @return list<string>
	 */
	public function getSecurity(): array;
}
