<?php

namespace KeepCloud\Vultr\Tests;

use KeepCloud\Vultr\VultrServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [VultrServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('vultr.token', 'test-token');
        $app['config']->set('vultr.endpoint', 'https://api.vultr.com/v2/');
        $app['config']->set('vultr.retry_delay', 0);
    }
}
