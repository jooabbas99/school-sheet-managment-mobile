<?php

use App\Livewire\Auth\Login;
use App\Services\Api\LookupsApi;
use App\Services\Auth\TokenStore;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('sends users without a token to login', function () {
    $this->get('/')->assertRedirect('/login');
    $this->get('/home')->assertRedirect('/login');
});

it('logs in, keeps the token and the user, and opens home', function () {
    Http::fake([API.'/login' => Http::response(['token' => '1|abc', 'user' => apiUser(['students.view'])])]);

    Livewire::test(Login::class)
        ->set('email', ' STAFF@school.test ')
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    Http::assertSent(fn (Request $r) => $r->url() === API.'/login'
        && $r['email'] === 'staff@school.test'
        && $r['device_name'] === 'android');

    expect(app(TokenStore::class)->get())->toBe('1|abc')
        ->and(session('api_user.name'))->toBe('محمد الموظف');
});

it('shows the server Arabic error under the email field', function () {
    Http::fake([API.'/login' => Http::response([
        'message' => 'البيانات المدخلة غير صحيحة.',
        'errors' => ['email' => ['بيانات الدخول غير صحيحة.']],
        'code' => 'validation_error',
    ], 422)]);

    Livewire::test(Login::class)
        ->set('email', 'staff@school.test')
        ->set('password', 'wrong')
        ->call('login')
        ->assertHasErrors(['email'])
        ->assertSee('بيانات الدخول غير صحيحة.')
        ->assertSet('password', '');

    expect(app(TokenStore::class)->get())->toBeNull();
});

it('validates the form in Arabic before calling the server', function () {
    Http::fake();

    Livewire::test(Login::class)
        ->call('login')
        ->assertHasErrors(['email' => 'required', 'password' => 'required'])
        ->assertSee('حقل البريد الإلكتروني مطلوب.');

    Http::assertNothingSent();
});

it('shows a clear Arabic message when offline', function () {
    Http::fake(fn () => throw new ConnectionException('no route'));

    Livewire::test(Login::class)
        ->set('email', 'staff@school.test')
        ->set('password', 'password')
        ->call('login')
        ->assertSet('offline', true)
        ->assertSee('تعذر الاتصال بالخادم');
});

it('refreshes the user at startup and opens home', function () {
    app(TokenStore::class)->put('t');
    Http::fake([API.'/me' => Http::response(['data' => apiUser(['reports.view'])])]);

    $this->get('/')->assertRedirect('/home');
    expect(session('api_user.permissions'))->toBe(['reports.view']);
});

it('returns to login when the saved token was revoked', function () {
    app(TokenStore::class)->put('revoked');
    Http::fake([API.'/me' => Http::response(['message' => 'يجب تسجيل الدخول أولًا.', 'errors' => [], 'code' => 'unauthenticated'], 401)]);

    $this->get('/')->assertRedirect('/login')->assertSessionHas('status', 'انتهت الجلسة. سجّل الدخول مرة أخرى.');
    expect(app(TokenStore::class)->get())->toBeNull();
});

it('shows the offline screen with retry when the server is unreachable at startup', function () {
    app(TokenStore::class)->put('t');
    Http::fake(fn () => throw new ConnectionException('down'));

    $this->get('/')->assertStatus(503)
        ->assertSee('لا يوجد اتصال بالإنترنت')
        ->assertSee('إعادة المحاولة');
});

it('logs out on the server and on the device', function () {
    signIn();
    Http::fake([API.'/logout' => Http::response(null, 204)]);

    $this->post('/logout')->assertRedirect('/login')->assertSessionHas('status', 'تم تسجيل الخروج.');

    Http::assertSent(fn (Request $r) => $r->url() === API.'/logout' && $r->hasHeader('Authorization', 'Bearer test-token'));
    expect(app(TokenStore::class)->get())->toBeNull()->and(session('api_user'))->toBeNull();
});

it('still logs out on the device when offline', function () {
    signIn();
    Http::fake(fn () => throw new ConnectionException('down'));

    $this->post('/logout')->assertRedirect('/login');
    expect(app(TokenStore::class)->get())->toBeNull();
});

it('keeps signed-in users away from the login screen', function () {
    signIn();

    $this->get('/login')->assertRedirect('/home');
});

it('removes downloaded files older than a week at startup', function () {
    Storage::disk('local')->put('downloads/old.xlsx', 'x');
    touch(Storage::disk('local')->path('downloads/old.xlsx'), now()->subDays(8)->getTimestamp());
    Storage::disk('local')->put('downloads/new.xlsx', 'x');

    $this->get('/')->assertRedirect('/login');

    Storage::disk('local')->assertMissing('downloads/old.xlsx');
    Storage::disk('local')->assertExists('downloads/new.xlsx');
});

it('reloads lookups on every app start', function () {
    app(TokenStore::class)->put('t');
    session()->put('api_lookups', ['at' => now()->timestamp, 'data' => ['institutes' => [], 'grades' => [], 'case_types' => []]]);
    Http::fake([API.'/me' => Http::response(['data' => apiUser(['students.view'])])]);

    $this->get('/')->assertRedirect('/home');

    expect(session('api_lookups'))->toBeNull();
});

it('keeps lookups for two minutes only', function () {
    signIn();
    Http::fake([API.'/lookups' => Http::sequence()
        ->push(['data' => ['institutes' => [['id' => 1, 'name' => 'قديم', 'has_logo' => false]], 'grades' => [], 'case_types' => []]])
        ->push(['data' => ['institutes' => [['id' => 1, 'name' => 'جديد', 'has_logo' => false]], 'grades' => [], 'case_types' => []]])]);
    $lookups = app(LookupsApi::class);

    expect($lookups->instituteOptions())->toBe([1 => 'قديم']);
    $this->travel(60)->seconds();
    expect($lookups->instituteOptions())->toBe([1 => 'قديم']);
    $this->travel(61)->seconds();
    expect($lookups->instituteOptions())->toBe([1 => 'جديد']);
});
