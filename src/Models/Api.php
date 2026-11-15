<?php

/**
 * Legacy v1 raw client.
 *
 * @author Edison Costa <edison@keepcloud.io>
 */

namespace KeepCloud\Vultr\Models;

use KeepCloud\Vultr\VultrClient;

/**
 * @deprecated 2.0 Use KeepCloud\Vultr\VultrClient::send().
 *             Returns the raw response body and never throws on HTTP errors.
 */
class Api
{
    /**
     * @param  array<string, mixed>  $query
     */
    public function get(string $url, array $query = []): string
    {
        return $this->client()->send('GET', $url, $query)->body();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function post(string $url, array $data = []): string
    {
        return $this->client()->send('POST', $url, [], $data)->body();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function put(string $url, array $data = []): string
    {
        return $this->client()->send('PUT', $url, [], $data)->body();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function patch(string $url, array $data = []): string
    {
        return $this->client()->send('PATCH', $url, [], $data)->body();
    }

    public function delete(string $url): string
    {
        return $this->client()->send('DELETE', $url)->body();
    }

    private function client(): VultrClient
    {
        return app(VultrClient::class);
    }
}
