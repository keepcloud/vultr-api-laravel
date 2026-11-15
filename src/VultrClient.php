<?php

namespace KeepCloud\Vultr;

use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use KeepCloud\Vultr\Exceptions\AuthenticationException;
use KeepCloud\Vultr\Exceptions\ConnectionException;
use KeepCloud\Vultr\Exceptions\VultrException;
use Throwable;

final class VultrClient
{
    /**
     * Methods safe to retry on server and connection errors.
     */
    private const IDEMPOTENT = ['GET', 'PUT', 'DELETE'];

    public function __construct(
        private readonly string $token,
        private readonly string $endpoint,
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 10,
        private readonly int $retries = 2,
        private readonly int $retryDelay = 500,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(array $config): self
    {
        return new self(
            token: (string) ($config['token'] ?? ''),
            endpoint: (string) ($config['endpoint'] ?? 'https://api.vultr.com/v2/'),
            timeout: (int) ($config['timeout'] ?? 30),
            connectTimeout: (int) ($config['connect_timeout'] ?? 10),
            retries: (int) ($config['retries'] ?? 2),
            retryDelay: (int) ($config['retry_delay'] ?? 500),
        );
    }

    /**
     * Get a new client using another API token (e.g. another Vultr account).
     */
    public function withToken(string $token): self
    {
        return new self(
            $token,
            $this->endpoint,
            $this->timeout,
            $this->connectTimeout,
            $this->retries,
            $this->retryDelay,
        );
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function get(string $path, array $query = []): mixed
    {
        return $this->decode($this->send('GET', $path, $query));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function post(string $path, array $data = []): mixed
    {
        return $this->decode($this->send('POST', $path, [], $data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function put(string $path, array $data = []): mixed
    {
        return $this->decode($this->send('PUT', $path, [], $data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function patch(string $path, array $data = []): mixed
    {
        return $this->decode($this->send('PATCH', $path, [], $data));
    }

    public function delete(string $path): mixed
    {
        return $this->decode($this->send('DELETE', $path));
    }

    /**
     * Lazily iterate every item of a cursor-paginated list endpoint.
     *
     * @param  array<string, mixed>  $query
     * @param  string|null  $key  Collection key in the response (inferred when null).
     * @return LazyCollection<int, mixed>
     */
    public function paginate(string $path, array $query = [], ?string $key = null, int $perPage = 100): LazyCollection
    {
        return LazyCollection::make(function () use ($path, $query, $key, $perPage) {
            $query['per_page'] = $perPage;
            $seen = [];

            while (true) {
                $page = $this->get($path, $query);

                if (! is_object($page)) {
                    return;
                }

                // Yield item by item: `yield from` would repeat keys across pages.
                foreach ($this->itemsOf($page, $key) as $item) {
                    yield $item;
                }

                $next = $page->meta->links->next ?? null;

                if (! is_string($next) || $next === '' || isset($seen[$next])) {
                    return;
                }

                $seen[$next] = true;
                $query['cursor'] = $next;
            }
        });
    }

    /**
     * @return array<int, mixed>
     */
    private function itemsOf(object $page, ?string $key): array
    {
        if ($key !== null) {
            return is_array($page->{$key} ?? null) ? $page->{$key} : [];
        }

        foreach (get_object_vars($page) as $name => $value) {
            if ($name !== 'meta' && is_array($value)) {
                return $value;
            }
        }

        return [];
    }

    /**
     * Send a request without throwing on HTTP error statuses.
     *
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>|null  $data
     *
     * @throws AuthenticationException when no token is configured
     * @throws ConnectionException when the API cannot be reached
     */
    public function send(string $method, string $path, array $query = [], ?array $data = null): Response
    {
        if ($this->token === '') {
            throw new AuthenticationException('Vultr API token is not configured.');
        }

        $method = strtoupper($method);

        $request = Http::baseUrl(rtrim($this->endpoint, '/').'/')
            ->withToken($this->token)
            ->acceptJson()
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->retry($this->retries + 1, $this->retryDelay, fn (Throwable $e) => $this->shouldRetry($e, $method), throw: false);

        $options = [];

        if ($query !== []) {
            $options['query'] = $query;
        }

        if ($data !== null) {
            // An empty body must be sent as a JSON object, not an empty array.
            $request->withBody($data === [] ? '{}' : (string) json_encode($data), 'application/json');
        }

        try {
            return $request->send($method, ltrim($path, '/'), $options);
        } catch (HttpConnectionException $e) {
            throw ConnectionException::fromHttp($e);
        }
    }

    private function shouldRetry(Throwable $e, string $method): bool
    {
        if ($e instanceof RequestException && $e->response->status() === 429) {
            return true;
        }

        if (! in_array($method, self::IDEMPOTENT, true)) {
            return false;
        }

        return $e instanceof HttpConnectionException
            || ($e instanceof RequestException && $e->response->serverError());
    }

    private function decode(Response $response): mixed
    {
        if ($response->failed()) {
            throw VultrException::fromResponse($response);
        }

        $body = $response->body();

        return $body === '' ? null : json_decode($body);
    }
}
