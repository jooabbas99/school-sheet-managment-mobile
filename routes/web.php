<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\SplashController;
use App\Livewire\Auth\Login;
use App\Livewire\ComingSoon;
use App\Livewire\Home;
use App\Livewire\More;
use App\Livewire\Reports;
use App\Livewire\Students;
use Illuminate\Support\Facades\Route;

Route::get('/', SplashController::class)->name('splash');
Route::view('/offline', 'offline')->name('offline');

Route::middleware('api.guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// api.permission without a parameter checks the section permission from App\Support\Navigation.
Route::middleware(['api.auth', 'api.permission'])->group(function () {
    Route::get('/home', Home::class)->name('home');
    Route::get('/more', More::class)->name('more');
    Route::post('/logout', LogoutController::class)->name('logout');
    Route::get('/files/{name}', FileController::class)->where('name', '[^/]+')->name('files.show');

    Route::get('/students', Students\Index::class)->name('students.index');
    Route::get('/students/create', Students\Form::class)->middleware('api.permission:students.create')->name('students.create');
    Route::get('/students/import', Students\Import::class)->middleware('api.permission:students.import')->name('students.import');
    Route::get('/students/{student}', Students\Show::class)->whereNumber('student')->middleware('api.permission:students.view')->name('students.show');
    Route::get('/students/{student}/edit', Students\Form::class)->whereNumber('student')->middleware('api.permission:students.update')->name('students.edit');

    Route::get('/reports', Reports\Index::class)->name('reports.index');
    Route::get('/reports/create', Reports\Wizard::class)->middleware('api.permission:reports.create')->name('reports.create');
    Route::get('/reports/{report}', Reports\Show::class)->whereNumber('report')->middleware('api.permission:reports.view')->name('reports.show');

    // Sections built in phase 7.
    $sections = [
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
