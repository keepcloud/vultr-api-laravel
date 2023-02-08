# Vultr Api V2 Laravel package

[![Total Downloads](https://poser.pugx.org/agiuscloud/vultr-api-laravel/downloads)](https://packagist.org/packages/agiuscloud/vultr-api-laravel)
[![License](https://poser.pugx.org/agiuscloud/vultr-api-laravel/license)](https://github.com/edisoncosta/vultr-api-laravel/blob/master/LICENSE)

A simple wrapper to get started with the Vultr V2 Api.

## Install
```
composer require keepcloud/vultr-api-laravel
```

After updating composer, add the ServiceProvider to the providers array in config/app.php
```
KeepCloud\Vultr\VultrServiceProvider::class
```

Optionally you can use the Facade. Add this to your facades:
```
'Vultr' => KeepCloud\Vultr\Facades\Vultr::class
```

## Publish config file
```
php artisan vendor:publish
```

## Add your personal access token to your config (/config/vultr.php) or env file
```
VULTR_TOKEN=Your_personal_access_token
```

You can create your token by visiting [your Vultr](https://cloud.vultr.com/profile/tokens) if you are using the newer vultr manager.

## Usage
Add to your class
```
use KeepCloud\Vultr\Controllers\Vultr;
```
To use
```
$vultr = new Vultr;

// list vultrs
$vultr->get('vultr/instances');

// create a new vultr
$vultr->post('vultr/instances', [
    "region" => "us-east-1a",
    "type" => "g5-standard-1"
]);

// update a vultr
$vultr->put('vultr/instances/999', [
    "label" => "new label"
]);

// delete a vultr
$vultr->delete('vultr/instances/999');

```

[Filtering & Sorting](https://developers.vultr.com/v4/filtering)
```
$vultr->get('vultr/distributions', [
    "vendor" => "Debian"
]);

$vultr->get('vultr/distributions', [
    "+or" =>
        [
            ["vendor" => "Debian"],
            ["deprecated" => true]
        ]
]);
```

Or, you can use the facade
```
Vultr::get('vultr/instances');
```

[Full API reference](https://developers.vultr.com/v4/introduction)

## License

This Vultr wrapper is open-sourced software licensed under the [MIT license](https://github.com/edisoncosta/vultr-api-laravel/blob/master/LICENSE).
