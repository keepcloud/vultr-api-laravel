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

it('works with the README Http::fake example', function () {
    Http::fake([
        'api.vultr.com/v2/account' => Http::response(['account' => ['name' => 'Test']]),
    ]);

    expect(Vultr::get('account')->account->name)->toBe('Test');
});

it('falls back to defaults for keys missing from a published v1 config', function () {
    // A 1.x published config only has endpoint and token.
    $client = VultrClient::fromConfig(['endpoint' => 'https://api.vultr.com/v2/', 'token' => 't']);
    $sequence = Http::sequence()->push(['e' => 1], 500)->push(['e' => 1], 500)->push(['ok' => true]);
    Http::fake(['*' => $sequence]);

    expect($client->get('account')->ok)->toBeTrue(); // default retries = 2
    Http::assertSentCount(3);
});
