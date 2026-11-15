# Vultr Api V2 Laravel package

[![Total Downloads](https://poser.pugx.org/keepcloud/vultr-api-laravel/downloads)](https://packagist.org/packages/keepcloud/vultr-api-laravel)
[![License](https://poser.pugx.org/keepcloud/vultr-api-laravel/license)](LICENSE)

A simple wrapper to get started with the [Vultr V2 API](https://www.vultr.com/api/).

## Install

```bash
composer require keepcloud/vultr-api-laravel
```

The service provider and the `Vultr` facade are registered automatically through Laravel package auto-discovery.

<details>
<summary>Laravel without auto-discovery</summary>

Add the service provider to the `providers` array in `config/app.php`:

```php
KeepCloud\Vultr\VultrServiceProvider::class,
```

And optionally the facade to the `aliases` array:

```php
'Vultr' => KeepCloud\Vultr\Facades\Vultr::class,
```
</details>

## Configuration

Add your personal access token to your `.env` file:

```dotenv
VULTR_TOKEN=Your_personal_access_token
```

You can create a token in the [Vultr customer portal](https://my.vultr.com/settings/#settingsapi).

Optional settings:

```dotenv
VULTR_ENDPOINT=https://api.vultr.com/v2/
VULTR_TIMEOUT=30
```

To customize the config file, publish it:

```bash
php artisan vendor:publish --tag=vultr-config
```

## Usage

Paths are relative to the API endpoint (`https://api.vultr.com/v2/`). Responses are returned as decoded JSON objects.

```php
use KeepCloud\Vultr\Facades\Vultr;

// account info
Vultr::get('account');

// list instances
Vultr::get('instances');

// list instances with query parameters (pagination, filters)
Vultr::get('instances', [
    'per_page' => 100,
    'region'   => 'ewr',
]);

// next page, using the cursor returned in meta.links.next
$page = Vultr::get('instances', ['per_page' => 100]);
Vultr::get('instances', ['per_page' => 100, 'cursor' => $page->meta->links->next]);

// create an instance
Vultr::post('instances', [
    'region' => 'ewr',
    'plan'   => 'vc2-1c-1gb',
    'os_id'  => 2136,
    'label'  => 'my-server',
]);

// update an instance
Vultr::patch('instances/{instance-id}', [
    'label' => 'new label',
]);

// instance actions
Vultr::post('instances/{instance-id}/reboot');

// delete an instance
Vultr::delete('instances/{instance-id}');
```

You can also resolve the client from the container or instantiate it directly:

```php
use KeepCloud\Vultr\Controllers\Vultr;

$vultr = app(Vultr::class);
// or
$vultr = new Vultr;

$vultr->get('regions');
```

[Full API reference](https://www.vultr.com/api/)

## License

This Vultr wrapper is open-sourced software licensed under the [MIT license](LICENSE).
