<?php

use App\Services\Auth\AutoTokenStore;
use App\Services\Auth\FileTokenStore;
use Illuminate\Support\Facades\Storage;

it('keeps the token encrypted on disk', function () {
    $store = new FileTokenStore;
    $store->put('secret-token');

    expect(Storage::disk('local')->get('auth/token'))->not->toContain('secret-token')
        ->and($store->get())->toBe('secret-token');

    $store->clear();
    expect($store->get())->toBeNull();
});

it('ignores a token file it cannot decrypt', function () {
    Storage::disk('local')->put('auth/token', 'garbage');

    expect((new FileTokenStore)->get())->toBeNull();
});

it('falls back to the file store outside the native app', function () {
    $store = app(AutoTokenStore::class);
    $store->put('abc');

    expect($store->get())->toBe('abc')
        ->and(Storage::disk('local')->exists('auth/token'))->toBeTrue();

    $store->clear();
    expect($store->get())->toBeNull();
});
