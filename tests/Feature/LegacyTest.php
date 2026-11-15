<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use KeepCloud\Vultr\Controllers\Vultr as LegacyVultr;
use KeepCloud\Vultr\Models\Api;

it('legacy controller returns error body instead of throwing', function () {
    Http::fake(['*' => Http::response(['error' => 'not found', 'status' => 404], 404)]);

    $result = (new LegacyVultr)->get('instances/x');

    expect($result->error)->toBe('not found');
});

it('legacy controller supports every verb', function (string $method, array $args) {
    Http::fake(['*' => Http::response(['ok' => true])]);

    expect((new LegacyVultr)->{$method}(...$args)->ok)->toBeTrue();

    Http::assertSent(fn (Request $r) => $r->method() === strtoupper($method));
})->with([
    ['get', ['instances', ['per_page' => 1]]],
    ['post', ['instances', ['region' => 'ewr']]],
    ['put', ['instances/x', ['label' => 'n']]],
    ['patch', ['instances/x', ['label' => 'n']]],
    ['delete', ['instances/x']],
]);

it('legacy static call works', function () {
    Http::fake(['*' => Http::response(['account' => ['name' => 'x']])]);

    expect(LegacyVultr::get('account')->account->name)->toBe('x');
});

it('legacy controller rejects unknown methods', function () {
    (new LegacyVultr)->foo();
})->throws(BadMethodCallException::class);

it('legacy Api returns raw string', function () {
    Http::fake(['*' => Http::response(['account' => ['name' => 'x']])]);

    $raw = (new Api)->get('account', ['a' => 1]);

    expect($raw)->toBe('{"account":{"name":"x"}}');
    Http::assertSent(fn (Request $r) => $r->url() === 'https://api.vultr.com/v2/account?a=1');
});

it('legacy Api returns empty string on 204', function () {
    Http::fake(['*' => Http::response('', 204)]);

    expect((new Api)->delete('instances/x'))->toBe('');
});

it('legacy classes read the token from config on every call like v1', function () {
    Http::fake(['*' => Http::response(['account' => []])]);

    LegacyVultr::get('account');
    config(['vultr.token' => 'tenant-b']);
    LegacyVultr::get('account');
    (new Api)->get('account');

    $tokens = Http::recorded()->map(fn ($pair) => $pair[0]->header('Authorization')[0])->all();

    expect($tokens)->toBe(['Bearer test-token', 'Bearer tenant-b', 'Bearer tenant-b']);
});
