<?php

use App\Exceptions\ApiException;
use App\Exceptions\OfflineException;
use App\Exceptions\UnauthenticatedException;
use App\Services\Api\ApiClient;
use App\Services\Auth\TokenStore;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends JSON with the bearer token to the configured base URL', function () {
    app(TokenStore::class)->put('abc');
    Http::fake([API.'/students*' => Http::response(['data' => [['id' => 1]]])]);

    $result = app(ApiClient::class)->get('students', ['search' => 'أحمد']);

    expect($result['data'][0]['id'])->toBe(1);
    Http::assertSent(fn (Request $request) => $request->url() === API.'/students?search=%D8%A3%D8%AD%D9%85%D8%AF'
        && $request->hasHeader('Authorization', 'Bearer abc')
        && $request->hasHeader('Accept', 'application/json'));
});

it('turns validation errors into an ApiException with field errors', function () {
    Http::fake([API.'/*' => Http::response([
        'message' => 'البيانات المدخلة غير صحيحة.',
        'errors' => ['national_id' => ['الرقم القومي غير صحيح.']],
        'code' => 'validation_error',
    ], 422)]);

    try {
        app(ApiClient::class)->post('students', []);
        $this->fail('Expected ApiException');
    } catch (ApiException $e) {
        expect($e->getMessage())->toBe('البيانات المدخلة غير صحيحة.')
            ->and($e->status)->toBe(422)
            ->and($e->errorCode)->toBe('validation_error')
            ->and($e->isValidation())->toBeTrue()
            ->and($e->errors['national_id'][0])->toBe('الرقم القومي غير صحيح.');
    }
});

it('uses an Arabic fallback message when the server sends none', function () {
    Http::fake([API.'/*' => Http::response('Bad Gateway', 502)]);

    expect(fn () => app(ApiClient::class)->get('me'))
        ->toThrow(ApiException::class, 'حدث خطأ في الخادم. حاول مرة أخرى لاحقًا.');
});

it('clears the token on 401', function () {
    app(TokenStore::class)->put('expired');
    Http::fake([API.'/*' => Http::response(['message' => 'يجب تسجيل الدخول أولًا.', 'errors' => [], 'code' => 'unauthenticated'], 401)]);

    expect(fn () => app(ApiClient::class)->get('me'))->toThrow(UnauthenticatedException::class);
    expect(app(TokenStore::class)->get())->toBeNull();
});

it('signs out a deactivated user', function () {
    app(TokenStore::class)->put('t');
    Http::fake([API.'/*' => Http::response(['message' => 'هذا الحساب موقوف.', 'errors' => [], 'code' => 'inactive_user'], 403)]);

    expect(fn () => app(ApiClient::class)->get('me'))->toThrow(UnauthenticatedException::class, 'هذا الحساب موقوف.');
    expect(app(TokenStore::class)->get())->toBeNull();
});

it('reports connection failures as offline', function () {
    Http::fake(fn () => throw new ConnectionException('timeout'));

    expect(fn () => app(ApiClient::class)->get('me'))
        ->toThrow(OfflineException::class, 'تعذر الاتصال بالخادم. تأكد من اتصالك بالإنترنت ثم حاول مرة أخرى.');
});

it('does not call the server when the device is offline', function () {
    setOnline(false);
    Http::fake();

    expect(fn () => app(ApiClient::class)->get('me'))->toThrow(OfflineException::class);
    Http::assertNothingSent();
});
