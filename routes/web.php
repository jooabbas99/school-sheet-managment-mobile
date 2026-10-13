<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\SplashController;
use App\Livewire\Activity;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Home;
use App\Livewire\Lookups;
use App\Livewire\More;
use App\Livewire\Reports;
use App\Livewire\Students;
use App\Livewire\Templates;
use App\Livewire\Users;
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

    Route::get('/templates', Templates\Index::class)->name('templates.index');
    Route::get('/templates/create', Templates\Builder::class)->middleware('api.permission:templates.manage')->name('templates.create');
    Route::get('/templates/{template}/edit', Templates\Builder::class)->whereNumber('template')->middleware('api.permission:templates.manage')->name('templates.edit');

    Route::get('/lookups', Lookups\Index::class)->name('lookups.index');
    Route::get('/institute-logo', Lookups\Logo::class)->name('institute.logo');

    Route::get('/users', Users\Index::class)->name('users.index');
    Route::get('/users/create', Users\Form::class)->middleware('api.permission:users.manage')->name('users.create');
    Route::get('/users/{user}/edit', Users\Form::class)->whereNumber('user')->middleware('api.permission:users.manage')->name('users.edit');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/activity', Activity\Index::class)->name('activity.index');
});
