<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Authentication;

interface GeneralAuthenticatedService
{
	public function executeAuthentication(): void;

	public function getCurrentAuthenticationValue(): mixed;

	public function setCurrentAuthentication(mixed $securityValue): void;
}
