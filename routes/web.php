<?php

use App\Http\Controllers\LogoutController;
use App\Http\Controllers\SplashController;
use App\Livewire\Auth\Login;
use App\Livewire\ComingSoon;
use App\Livewire\Home;
use App\Livewire\More;
use Illuminate\Support\Facades\Route;

Route::get('/', SplashController::class)->name('splash');
Route::view('/offline', 'offline')->name('offline');

Route::middleware('api.guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware(['api.auth', 'api.permission'])->group(function () {
    Route::get('/home', Home::class)->name('home');
    Route::get('/more', More::class)->name('more');
    Route::post('/logout', LogoutController::class)->name('logout');

    // Sections built in phases 6 and 7.
    $sections = [
        'students' => 'app.nav.students',
        'reports' => 'app.nav.reports',
        'templates' => 'app.nav.templates',
        'users' => 'app.nav.users',
        'lookups' => 'app.nav.lookups',
        'activity' => 'app.nav.activity',
    ];

    foreach ($sections as $path => $titleKey) {
        Route::get("/{$path}", ComingSoon::class)->defaults('titleKey', $titleKey)->name("{$path}.index");
    }

    Route::get('/dashboard', ComingSoon::class)->defaults('titleKey', 'app.nav.dashboard')->name('dashboard');
});
