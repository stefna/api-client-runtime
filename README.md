# Stefna Api Client Runtime

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stefna/di.svg)](https://packagist.org/packages/stefna/di)
[![Software License](https://img.shields.io/github/license/stefna/di.svg)](LICENSE)

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
		'production' => 'https://api.example.com',
		'staging' => 'https://staging.api.example.com',
	];
	protected string $selectedBaseUri = 'production';
	protected SecurityScheme $securityScheme;
	protected SecurityValue $securityValue;

	public function __construct(SecurityValue $token)
	{
		$this->securityScheme = new ApiKeySecurityScheme('access-token', 'X-Api-Token', 'header');
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
	public function getNews(string $lang): array
	{
		$response = $this->doRequest(new \Stefna\ApiClientRuntime\Endpoint\Endpoint(
			'GET',
			'/news/' . $lang,
			security: ['access-token'],
		));

		return $this->parseJsonResponse($response);
	}

	public function sendNotification(string $to, string $from, string $text)
	{
		$response = $this->doRequest(new \Stefna\ApiClientRuntime\Endpoint\Endpoint(
			'POST',
			'/notification',
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

## Consuming api service

When consuming the api-client you can create it with 2 different methods

### Simple creation

The simple way is to use the static `create` method.

But for that method to work you need to have `nyholm/psr7` and `kriswallsmith/buzz` installed since that's the default
psr implementations we use

```php
$service = Service::create(new ServerConfiguration(...));

// use service
```

### Create with custom psr implementations

If you want you can provide your own Client and Request implementations

```php
$service = new Service(
	new ServerConfiguration(...),
	new GuzzleHttp\Client(),
	new GuzzleHttp\Psr7\HttpFactory(),
);
```

## License

View the [LICENSE](LICENSE) file attach to this project.

