<?php

/**
 * Vultr api facade
 *
 * @author Edison Costa <edison@keepcloud.io>
 */

namespace KeepCloud\Vultr\Facades;

use Illuminate\Support\Facades\Facade;
use KeepCloud\Vultr\VultrClient;

/**
 * @method static mixed get(string $path, array<string, mixed> $query = [])
 * @method static mixed post(string $path, array<string, mixed> $data = [])
 * @method static mixed put(string $path, array<string, mixed> $data = [])
 * @method static mixed patch(string $path, array<string, mixed> $data = [])
 * @method static mixed delete(string $path)
 * @method static \Illuminate\Support\LazyCollection<int, mixed> paginate(string $path, array<string, mixed> $query = [], ?string $key = null, int $perPage = 100)
 * @method static \KeepCloud\Vultr\VultrClient withToken(string $token)
 * @method static \Illuminate\Http\Client\Response send(string $method, string $path, array<string, mixed> $query = [], ?array<string, mixed> $data = null)
 *
 * @see VultrClient
 */
class Vultr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return VultrClient::class;
    }
}
