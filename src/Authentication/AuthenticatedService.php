<?php declare(strict_types=1);

namespace Stefna\ApiClientRuntime\Authentication;

interface AuthenticatedService
{
	public function executeAuthentication(): void;

	public function getCurrentAuthenticationValue(): mixed;

	public function setCurrentAuthentication(mixed $securityValue): void;
}
