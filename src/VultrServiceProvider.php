<?php
/**
 * Vultr api service provider
 *
 * @package keepcloud/vultr-api-laravel
 * @author Edison Costa <edison@keepcloud.io>
 */
namespace KeepCloud\Vultr;

use Illuminate\Support\ServiceProvider;
use KeepCloud\Vultr\Controllers\Vultr;

class VultrServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/vultr.php' => config_path('vultr.php'),
            ], 'vultr-config');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/vultr.php', 'vultr');

        $this->app->singleton(Vultr::class, function () {
            return new Vultr;
        });

        $this->app->alias(Vultr::class, 'Vultr');
    }
}
