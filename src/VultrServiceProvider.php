<?php

/**
 * Vultr api service provider
 *
 * @author Edison Costa <edison@keepcloud.io>
 */

namespace KeepCloud\Vultr;

use Illuminate\Support\ServiceProvider;

class VultrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/vultr.php', 'vultr');

        $this->app->singleton(VultrClient::class, fn ($app) => VultrClient::fromConfig($app['config']['vultr']));

        $this->app->alias(VultrClient::class, 'Vultr');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/vultr.php' => config_path('vultr.php'),
            ], 'vultr-config');
        }
    }
}
