<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use KeepCloud\Vultr\Facades\Vultr;
use KeepCloud\Vultr\VultrClient;

it('resolves VultrClient singleton', function () {
    expect(Vultr::getFacadeRoot())->toBeInstanceOf(VultrClient::class)
        ->and(Vultr::getFacadeRoot())->toBe(app('Vultr'))
        ->and(app('Vultr'))->toBe(app(VultrClient::class));
});

it('reads token and endpoint from config', function () {
    config(['vultr.token' => 'from-config', 'vultr.endpoint' => 'https://example.test/v2']);
    app()->forgetInstance(VultrClient::class);
    Vultr::clearResolvedInstances();
    Http::fake(['*' => Http::response(['account' => []])]);

    Vultr::get('account');

    Http::assertSent(fn (Request $r) => $r->url() === 'https://example.test/v2/account'
        && $r->hasHeader('Authorization', 'Bearer from-config'));
});

it('works like v1 on success', function () {
    Http::fake(['*' => Http::response(['instances' => [['id' => 'a']]])]);

    expect(Vultr::get('instances')->instances[0]->id)->toBe('a');
});

it('exposes paginate and withToken', function () {
    Http::fake(['*' => Http::response(['instances' => [['id' => 'a']], 'meta' => ['links' => ['next' => '']]])]);

    expect(Vultr::paginate('instances'))->toBeInstanceOf(LazyCollection::class)
        ->and(Vultr::withToken('other'))->toBeInstanceOf(VultrClient::class)
        ->and(Vultr::withToken('other'))->not->toBe(Vultr::getFacadeRoot());
});
