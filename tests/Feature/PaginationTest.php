<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;
use KeepCloud\Vultr\VultrClient;

function paginator(): VultrClient
{
    return VultrClient::fromConfig(config('vultr'));
}

function page(string $key, array $ids, string $next): array
{
    return [
        $key => array_map(fn ($id) => ['id' => $id], $ids),
        'meta' => ['total' => 3, 'links' => ['next' => $next, 'prev' => '']],
    ];
}

it('walks all pages', function () {
    Http::fake(['*' => Http::sequence()
        ->push(page('instances', ['a', 'b'], 'abc'))
        ->push(page('instances', ['c'], ''))]);

    $lazy = paginator()->paginate('instances');
    $items = $lazy->all();

    expect($lazy)->toBeInstanceOf(LazyCollection::class)
        ->and(array_map(fn ($i) => $i->id, $items))->toBe(['a', 'b', 'c'])
        ->and($items[0])->toBeInstanceOf(stdClass::class);

    $requests = Http::recorded()->map(fn ($pair) => $pair[0])->values();
    expect($requests)->toHaveCount(2)
        ->and($requests[0]->url())->toBe('https://api.vultr.com/v2/instances?per_page=100')
        ->and($requests[1]->url())->toBe('https://api.vultr.com/v2/instances?per_page=100&cursor=abc');
});

it('sends exactly one request per page', function () {
    Http::fake(['*' => Http::sequence()
        ->push(page('instances', ['a', 'b'], 'abc'))
        ->push(page('instances', ['c'], ''))]);

    paginator()->paginate('instances', ['region' => 'ewr'], perPage: 50)->all();

    Http::assertSentCount(2);
    Http::assertSent(fn (Request $r) => $r->url() === 'https://api.vultr.com/v2/instances?region=ewr&per_page=50&cursor=abc');
});

it('infers the collection key', function () {
    Http::fake(['*' => Http::response(page('os', [1, 2], ''))]);

    expect(paginator()->paginate('os')->pluck('id')->all())->toBe([1, 2]);
});

it('honours explicit key', function () {
    Http::fake(['*' => Http::response([
        'meta' => ['links' => ['next' => '']],
        'other' => [['id' => 'x']],
        'records' => [['id' => 'r1']],
    ])]);

    expect(paginator()->paginate('domains/example.com/records', key: 'records')->pluck('id')->all())
        ->toBe(['r1']);
});

it('stops on repeated cursor', function () {
    Http::fake(['*' => Http::sequence()
        ->push(page('instances', ['a'], 'abc'))
        ->push(page('instances', ['b'], 'abc'))
        ->push(page('instances', ['c'], ''))]);

    expect(paginator()->paginate('instances')->pluck('id')->all())->toBe(['a', 'b']);

    Http::assertSentCount(2);
});

it('is lazy', function () {
    Http::fake(['*' => Http::sequence()
        ->push(page('instances', ['a', 'b'], 'abc'))
        ->push(page('instances', ['c'], ''))]);

    $items = paginator()->paginate('instances');
    Http::assertNothingSent();

    $items->take(1)->all();
    Http::assertSentCount(1);
});

it('stops when meta is missing', function () {
    Http::fake(['*' => Http::response(['instances' => [['id' => 'a']]])]);

    expect(paginator()->paginate('instances')->pluck('id')->all())->toBe(['a']);

    Http::assertSentCount(1);
});

it('yields nothing on an empty list', function () {
    Http::fake(['*' => Http::response(page('instances', [], ''))]);

    expect(paginator()->paginate('instances')->all())->toBe([]);
});

it('keeps filters written in the path', function () {
    Http::fake(['*' => Http::response(page('instances', ['a'], ''))]);

    paginator()->paginate('instances?label=web')->all();

    Http::assertSent(fn (Request $r) => $r->url() === 'https://api.vultr.com/v2/instances?label=web&per_page=100');
});
