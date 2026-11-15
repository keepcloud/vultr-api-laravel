<?php

/**
 * Legacy v1 client.
 *
 * @author Edison Costa <edison@keepcloud.io>
 */

namespace KeepCloud\Vultr\Controllers;

use BadMethodCallException;
use KeepCloud\Vultr\VultrClient;

/**
 * @deprecated 2.0 Use KeepCloud\Vultr\Facades\Vultr or KeepCloud\Vultr\VultrClient.
 *             Kept for v1 compatibility: never throws on HTTP errors and
 *             returns the decoded error body instead.
 *
 * @method mixed get(string $url, array<string, mixed> $query = [])
 * @method mixed post(string $url, array<string, mixed> $data = [])
 * @method mixed put(string $url, array<string, mixed> $data = [])
 * @method mixed patch(string $url, array<string, mixed> $data = [])
 * @method mixed delete(string $url)
 * @method static mixed get(string $url, array<string, mixed> $query = [])
 * @method static mixed post(string $url, array<string, mixed> $data = [])
 * @method static mixed put(string $url, array<string, mixed> $data = [])
 * @method static mixed patch(string $url, array<string, mixed> $data = [])
 * @method static mixed delete(string $url)
 */
class Vultr
{
    private const VERBS = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return self::call($name, $arguments);
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return self::call($name, $arguments);
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    private static function call(string $name, array $arguments): mixed
    {
        $name = strtolower($name);

        if (! in_array($name, self::VERBS, true)) {
            throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $name));
        }

        $path = (string) ($arguments[0] ?? '');
        $payload = $arguments[1] ?? [];

        [$query, $data] = match ($name) {
            'get' => [$payload, null],
            'delete' => [[], null],
            default => [[], $payload],
        };

        $response = VultrClient::fromConfig((array) config('vultr'))->send($name, $path, $query, $data);

        return json_decode($response->body());
    }
}
