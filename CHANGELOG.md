# Changelog

## v2.0.0

Requires PHP 8.2+ and Laravel 11, 12 or 13. See [UPGRADE.md](UPGRADE.md).

### Added
- `KeepCloud\Vultr\VultrClient`, built on Laravel's HTTP client; the `Vultr` facade now resolves it.
- Typed exceptions: `AuthenticationException`, `NotFoundException`, `ValidationException`, `RateLimitException` (with `retryAfter`), `ServerException` and `ConnectionException`, all extending `VultrException`.
- Automatic retries: 429 for every method; 5xx and connection errors for GET, PUT and DELETE only.
- `paginate()` returning a `LazyCollection` over cursor-paginated endpoints.
- `withToken()` for using several Vultr accounts.
- `send()` returning the raw `Response` without throwing.
- Config keys `connect_timeout`, `retries` and `retry_delay`.
- Pest test suite, Pint, Larastan and GitHub Actions CI.

### Changed
- The facade throws on API errors instead of returning the error body.
- A missing token throws `AuthenticationException` before any request.

### Deprecated
- `KeepCloud\Vultr\Controllers\Vultr` and `KeepCloud\Vultr\Models\Api` (still working, with their 1.x behaviour).

### Removed
- Support for PHP < 8.2 and Laravel < 11.
- Direct cURL usage.

## v1.1.0

### Fixed
- `Vultr` facade now works: the client is bound in the container (`Vultr` alias and `KeepCloud\Vultr\Controllers\Vultr`).
- Default config is merged, so the package works without publishing `config/vultr.php`.
- `get()` now accepts query parameters (`per_page`, `cursor`, filters).
- POST/PUT/PATCH without data send `{}` instead of `[]`.
- Leading/trailing slashes in endpoint and paths are normalized.
- The client no longer extends `App\Http\Controllers\Controller`.
- Removed `curl_close()` deprecation on PHP 8.5.

### Added
- Laravel package auto-discovery (service provider and facade).
- `timeout` config option (`VULTR_TIMEOUT`, default 30s) and a 10s connect timeout.
- `vultr-config` publish tag.
- Explicit `illuminate/support`, `ext-curl` and `ext-json` requirements.

### Changed
- README rewritten with Vultr V2 examples.
