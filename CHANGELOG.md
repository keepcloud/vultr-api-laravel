# Changelog

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
