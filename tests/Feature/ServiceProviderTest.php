<?php

it('merges default config without publishing', function () {
    expect(config('vultr.endpoint'))->toBe('https://api.vultr.com/v2/')
        ->and(config('vultr.retries'))->toBe(2)
        ->and(config('vultr.connect_timeout'))->toBe(10);
});
