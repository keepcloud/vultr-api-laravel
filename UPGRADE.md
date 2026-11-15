# Upgrade Guide

## From 1.x to 2.0

### Requirements

- PHP **8.2+** (was 7.0+)
- Laravel **11, 12 or 13** (was 5.5+)

### The `Vultr` facade throws on errors

In 1.x, `Vultr::get()` returned the decoded body even for failed requests, so errors had to be checked by hand:

```php
// 1.x
$result = Vultr::get("instances/{$id}");

if (isset($result->error)) {
    // handle error
}
```

In 2.0 the facade resolves `KeepCloud\Vultr\VultrClient` and throws a `KeepCloud\Vultr\Exceptions\VultrException` subclass:

```php
// 2.0
use KeepCloud\Vultr\Exceptions\NotFoundException;
use KeepCloud\Vultr\Exceptions\VultrException;

try {
    $result = Vultr::get("instances/{$id}");
} catch (NotFoundException $e) {
    // 404
} catch (VultrException $e) {
    // any other API error: $e->getMessage(), $e->getCode() (HTTP status), $e->response
}
```

Successful responses keep the same shape (`stdClass`, `null` for empty bodies), so code that only handles the success path does not change.

A missing `VULTR_TOKEN` now throws `AuthenticationException` before any request is sent.

### Retries

Requests are now retried automatically: `429` for every method, `5xx` and connection errors for `GET`, `PUT` and `DELETE`. Set `VULTR_RETRIES=0` to disable.

### Published config

If you published `config/vultr.php`, add the new keys (or re-publish with `php artisan vendor:publish --tag=vultr-config --force`):

```php
'timeout' => (int) env('VULTR_TIMEOUT', 30),
'connect_timeout' => (int) env('VULTR_CONNECT_TIMEOUT', 10),
'retries' => (int) env('VULTR_RETRIES', 2),
'retry_delay' => (int) env('VULTR_RETRY_DELAY', 500),
```

Missing keys fall back to these defaults.

### Deprecated classes

`KeepCloud\Vultr\Controllers\Vultr` and `KeepCloud\Vultr\Models\Api` still work with their 1.x behaviour (no exceptions on HTTP errors), but are deprecated and will be removed in a future major version. Migrate to the `Vultr` facade or inject `VultrClient`.

Two edge cases changed in the legacy classes too:

- Connection failures throw `ConnectionException` (1.x returned `null` from `Controllers\Vultr` and `false` from `Models\Api`). `Models\Api` methods now always return a `string`.
- A missing token throws `AuthenticationException` instead of sending an unauthenticated request.
