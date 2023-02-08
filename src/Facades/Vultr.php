<?php
/**
 * Vultr api facade
 *
 * @package keepcloud/vultr-api-laravel
 * @author Edison Costa <edison@keepcloud.io>
 */
namespace KeepCloud\Vultr\Facades;

use Illuminate\Support\Facades\Facade;

class Vultr extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Vultr';
    }
}
