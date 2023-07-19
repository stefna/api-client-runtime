# Stefna Api Client Runtime

Base classes to make it easier to create apis.

Used by Stefna OpenApi Generator among others.

## Usage

To create an api-client you will need 2 classes

First is a `ServerConfiguration` class that contains information about the api you want to connect to

Example:

```php
class ServerConfiguration extends AbstractServerConfiguration
{
	/** @var string[] */
	protected array $serverUris = [
		'production' => 'https://api.stefna.is',
		'staging' => 'https://staging.api.stefna.is',
	];
	protected string $selectedBaseUri = 'production';
	protected SecurityScheme $securityScheme;
	protected SecurityValue $securityValue;

	public function __construct(SecurityValue $token)
	{
		$this->securityScheme = new ApiKeySecurityScheme('access-token', 'X-Access-Token', 'header');
		$this->securityValue = $token;
	}

	public function getBaseUri(): string
	{
		return $this->serverUris[$this->selectedBaseUri];
	}

	public function selectServer(string $name): void
	{
		$this->selectedBaseUri = $name;
	}

	public function getSecurityScheme(string $ref): ?SecurityScheme
	{
		return $this->securityScheme;
	}

	public function getSecurityValue(string $ref): ?SecurityValue
	{
		return $this->securityValue;
	}
}
```

And the second one is the actual api-client

```php

final class Service extends AbstractService
{
	public function getTos(string $lang): bool
	{
		$response = $this->doRequest(new \Stefna\ApiClientRuntime\Endpoint\Endpoint(
			'GET',
			'/tos/' . $lang,
			security: ['access-token'],
		));

		return $this->parseJsonResponse($response);
	}

	public function sendSms(string $to, string $from, string $text)
	{
		$response = $this->doRequest(new \Stefna\ApiClientRuntime\Endpoint\Endpoint(
			'POST',
			'/sms',
			new \Stefna\ApiClientRuntime\RequestBody\JsonData([
				'to' => $to,
				'from' => $from,
				'text' => $text,
			]),
			security: ['access-token'],
		));

		return $this->parseJsonResponse($response);
	}
	
	public function sendPostData(array $postData): bool
	{
		$response = $this->doRequest(new \Stefna\ApiClientRuntime\Endpoint\Endpoint(
			'POST',
			'/post-endpoint',
			new \Stefna\ApiClientRuntime\RequestBody\PostData($postData)
			security: ['access-token'],
		));

		return $this->parseJsonResponse($response);
	}

	public static function createWithToken(string $token): self
	{
		return static::create(new ServerConfiguration(AuthSecurityValue::raw($token)));
	}
}
```

## License

View the [LICENSE](LICENSE) file attach to this project.

