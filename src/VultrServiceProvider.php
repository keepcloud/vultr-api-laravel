<?php
/**
 * Vultr api service provider
 *
 * @package keepcloud/vultr-api-laravel
 * @author Edison Costa <edison@keepcloud.io>
 */
namespace KeepCloud\Vultr;

use Illuminate\Support\ServiceProvider;

class VultrServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/vultr.php' => config_path('vultr.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
