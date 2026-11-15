# Vultr Api V2 Laravel package

[![Tests](https://github.com/keepcloud/vultr-api-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/keepcloud/vultr-api-laravel/actions/workflows/tests.yml)
[![Total Downloads](https://poser.pugx.org/keepcloud/vultr-api-laravel/downloads)](https://packagist.org/packages/keepcloud/vultr-api-laravel)
[![License](https://poser.pugx.org/keepcloud/vultr-api-laravel/license)](LICENSE)

A Laravel client for the [Vultr V2 API](https://www.vultr.com/api/), built on Laravel's HTTP client: typed exceptions, automatic retries, lazy cursor pagination and multiple accounts.

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13

> Laravel 11 is end of life and Composer blocks its releases because of open security advisories. Prefer Laravel 12+.

Upgrading from 1.x? See [UPGRADE.md](UPGRADE.md).

## Install

```bash
composer require keepcloud/vultr-api-laravel
```

The service provider and the `Vultr` facade are registered automatically.

## Configuration

Add your personal access token to `.env` (create one in the [Vultr customer portal](https://my.vultr.com/settings/#settingsapi)):

```dotenv
VULTR_TOKEN=your-personal-access-token
```

Optional settings and their defaults:

```dotenv
VULTR_ENDPOINT=https://api.vultr.com/v2/
VULTR_TIMEOUT=30            # seconds
VULTR_CONNECT_TIMEOUT=10    # seconds
VULTR_RETRIES=2             # retries after the first attempt
VULTR_RETRY_DELAY=500       # milliseconds
```

To customize the config file, publish it:

```bash
php artisan vendor:publish --tag=vultr-config
```

## Usage

Paths are relative to the API endpoint. Responses are decoded JSON objects (`stdClass`); empty responses (e.g. `204 No Content`) return `null`.

```php
use KeepCloud\Vultr\Facades\Vultr;

Vultr::get('account');

Vultr::get('instances', ['region' => 'ewr', 'per_page' => 50]);

$instance = Vultr::post('instances', [
    'region' => 'ewr',
    'plan'   => 'vc2-1c-1gb',
    'os_id'  => 2136,
    'label'  => 'my-server',
])->instance;

Vultr::patch("instances/{$instance->id}", ['label' => 'new label']);

Vultr::post("instances/{$instance->id}/reboot");

Vultr::delete("instances/{$instance->id}");
```

You can also inject the client:

```php
use KeepCloud\Vultr\VultrClient;

public function __construct(private VultrClient $vultr) {}
```

### Pagination

`paginate()` walks every page of a list endpoint lazily, following `meta.links.next`:

```php
Vultr::paginate('instances')->each(function ($instance) {
    echo $instance->label;
});

Vultr::paginate('instances', ['region' => 'ewr'])
    ->filter(fn ($i) => $i->power_status === 'running')
    ->count();

// The collection key is inferred; pass it when the response has several lists.
Vultr::paginate('domains/example.com/records', key: 'records', perPage: 200);
```

Pages are only requested as the collection is iterated.

### Multiple accounts

```php
Vultr::withToken($customer->vultr_token)->get('instances');
```

`withToken()` returns a new client; the default one is not changed.

The `Vultr` facade is a singleton that reads `config('vultr')` once, when first resolved. Changing `vultr.token` at runtime does not affect it: use `withToken()` for per-tenant tokens.

### Errors

Failed requests throw an exception extending `KeepCloud\Vultr\Exceptions\VultrException`. The message is the API's `error` field, the code is the HTTP status and `$e->response` holds the response.

| Status | Exception |
|---|---|
| 401, 403 | `AuthenticationException` (also thrown when no token is configured) |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| 429 | `RateLimitException` (`$e->retryAfter` in seconds, when sent) |
| 5xx | `ServerException` |
| connection failure | `ConnectionException` |

```php
use KeepCloud\Vultr\Exceptions\NotFoundException;
use KeepCloud\Vultr\Exceptions\VultrException;

try {
    Vultr::get("instances/{$id}");
} catch (NotFoundException) {
    // gone
} catch (VultrException $e) {
    report($e);
}
```

### Retries

Rate limited requests (`429`) are retried for every method. Server errors (`5xx`) and connection failures are retried only for `GET`, `PUT` and `DELETE`, so a `POST` is never sent twice (e.g. creating two instances).

### Raw responses

`send()` returns the `Illuminate\Http\Client\Response` without throwing on HTTP errors:

```php
$response = Vultr::send('GET', 'instances', ['per_page' => 10]);

$response->status();
$response->json('instances');
```

### Testing

The client uses Laravel's HTTP client, so `Http::fake()` works:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'api.vultr.com/v2/account' => Http::response(['account' => ['name' => 'Test']]),
]);

expect(Vultr::get('account')->account->name)->toBe('Test');
```

## Legacy API (deprecated)

The 1.x classes still work and keep their behaviour (they never throw on HTTP errors and return the decoded error body):

```php
use KeepCloud\Vultr\Controllers\Vultr;

(new Vultr)->get('instances');
Vultr::get('instances');
```

`KeepCloud\Vultr\Models\Api` returns the raw response body as a string. Both will be removed in a future major version.

## Development

```bash
composer test     # Pest
composer lint     # Pint
composer analyse  # Larastan
```

## License

This Vultr wrapper is open-sourced software licensed under the [MIT license](LICENSE).
