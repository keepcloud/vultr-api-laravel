<?php

use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use KeepCloud\Vultr\Exceptions\AuthenticationException;
use KeepCloud\Vultr\Exceptions\ConnectionException;
use KeepCloud\Vultr\Exceptions\NotFoundException;
use KeepCloud\Vultr\Exceptions\RateLimitException;
use KeepCloud\Vultr\Exceptions\ServerException;
use KeepCloud\Vultr\Exceptions\ValidationException;
use KeepCloud\Vultr\Exceptions\VultrException;
use KeepCloud\Vultr\VultrClient;

function client(array $overrides = []): VultrClient
{
    return VultrClient::fromConfig(array_merge(config('vultr'), $overrides));
}

it('sends bearer token and json headers', function () {
    Http::fake(['*' => Http::response(['account' => []])]);

    client()->get('account');

    Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer test-token')
        && $r->hasHeader('Accept', 'application/json'));
});

it('builds urls with query params', function () {
    Http::fake(['*' => Http::response(['instances' => []])]);

    client()->get('/instances', ['per_page' => 100]);

    Http::assertSent(fn (Request $r) => $r->url() === 'https://api.vultr.com/v2/instances?per_page=100'
        && $r->method() === 'GET');
});

it('decodes json to stdClass', function () {
    Http::fake(['*' => Http::response(['account' => ['name' => 'x']])]);

    $result = client()->get('account');

    expect($result)->toBeInstanceOf(stdClass::class)
        ->and($result->account->name)->toBe('x');
});

it('returns null on 204', function () {
    Http::fake(['*' => Http::response('', 204)]);

    expect(client()->delete('instances/1'))->toBeNull();

    Http::assertSent(fn (Request $r) => $r->method() === 'DELETE'
        && $r->url() === 'https://api.vultr.com/v2/instances/1');
});

it('sends {} for empty post body', function () {
    Http::fake(['*' => Http::response('', 204)]);

    client()->post('instances/1/reboot');

    Http::assertSent(fn (Request $r) => $r->method() === 'POST' && $r->body() === '{}');
});

it('sends json body on post, put and patch', function (string $method) {
    Http::fake(['*' => Http::response(['ok' => true])]);

    client()->{$method}('instances/1', ['tags' => ['a', 'b']]);

    Http::assertSent(fn (Request $r) => $r->method() === strtoupper($method)
        && $r->body() === '{"tags":["a","b"]}'
        && $r->hasHeader('Content-Type', 'application/json'));
})->with(['post', 'put', 'patch']);

it('maps status to exceptions', function (int $status, string $class) {
    Http::fake(['*' => Http::response(['error' => 'boom', 'status' => $status], $status)]);

    try {
        client(['retries' => 0])->get('instances');
        $this->fail('Expected exception');
    } catch (VultrException $e) {
        expect($e)->toBeInstanceOf($class)
            ->and(get_class($e))->toBe($class)
            ->and($e->getCode())->toBe($status)
            ->and($e->response?->status())->toBe($status);
    }
})->with([
    [401, AuthenticationException::class],
    [403, AuthenticationException::class],
    [404, NotFoundException::class],
    [400, ValidationException::class],
    [422, ValidationException::class],
    [429, RateLimitException::class],
    [500, ServerException::class],
    [418, VultrException::class],
]);

it('uses api error message', function () {
    Http::fake(['*' => Http::response(['error' => 'Invalid API token.', 'status' => 401], 401)]);

    client()->get('account');
})->throws(AuthenticationException::class, 'Invalid API token.');

it('falls back to HTTP status on non-json body', function () {
    Http::fake(['*' => Http::response('<html>Bad Gateway</html>', 502, ['Content-Type' => 'text/html'])]);

    client(['retries' => 0])->get('account');
})->throws(ServerException::class, 'HTTP 502');

it('exposes retry after on 429', function () {
    Http::fake(['*' => Http::response(['error' => 'rate'], 429, ['Retry-After' => '3'])]);

    try {
        client(['retries' => 0])->get('account');
        $this->fail('Expected exception');
    } catch (RateLimitException $e) {
        expect($e->retryAfter)->toBe(3);
    }
});

it('throws when token is empty', function () {
    Http::fake();

    try {
        client(['token' => ''])->get('account');
        $this->fail('Expected exception');
    } catch (AuthenticationException $e) {
        expect($e->getMessage())->toBe('Vultr API token is not configured.');
    }

    Http::assertNothingSent();
});

it('retries GET on 500', function () {
    Http::fake(['*' => Http::sequence()
        ->push(['error' => 'down'], 500)
        ->push(['account' => ['name' => 'x']], 200)]);

    expect(client()->get('account')->account->name)->toBe('x');

    Http::assertSentCount(2);
});

it('does not retry POST on 500', function () {
    Http::fake(['*' => Http::sequence()
        ->push(['error' => 'down'], 500)
        ->push(['instance' => []], 201)]);

    expect(fn () => client()->post('instances', ['region' => 'ewr']))
        ->toThrow(ServerException::class);

    Http::assertSentCount(1);
});

it('retries POST on 429', function () {
    Http::fake(['*' => Http::sequence()
        ->push(['error' => 'rate'], 429)
        ->push(['instance' => ['id' => 'a']], 201)]);

    expect(client()->post('instances', ['region' => 'ewr'])->instance->id)->toBe('a');

    Http::assertSentCount(2);
});

it('wraps connection errors', function () {
    Http::fake(fn () => throw new HttpConnectionException('down'));

    client()->get('account');
})->throws(ConnectionException::class, 'down');

it('does not retry POST on connection error', function () {
    $calls = 0;
    Http::fake(function () use (&$calls) {
        $calls++;
        throw new HttpConnectionException('down');
    });

    expect(fn () => client()->post('instances'))->toThrow(ConnectionException::class);
    expect($calls)->toBe(1);
});

it('uses a different token per call without mutating the singleton', function () {
    Http::fake(['*' => Http::response(['account' => []])]);
    $client = client();

    $client->withToken('other')->get('account');
    $client->get('account');

    $tokens = Http::recorded()->map(fn ($pair) => $pair[0]->header('Authorization')[0])->all();

    expect($tokens)->toBe(['Bearer other', 'Bearer test-token']);
});

it('keeps other settings when switching token', function () {
    Http::fake(['*' => Http::response(['account' => []])]);

    client(['endpoint' => 'https://example.test/v2'])->withToken('other')->get('account');

    Http::assertSent(fn (Request $r) => $r->url() === 'https://example.test/v2/account');
});
