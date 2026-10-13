<?php

use App\Exceptions\OfflineException;
use App\Exceptions\UnauthenticatedException;
use App\Http\Middleware\EnsureApiAuthenticated;
use App\Http\Middleware\EnsureApiPermission;
use App\Http\Middleware\RedirectIfApiAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.auth' => EnsureApiAuthenticated::class,
            'api.guest' => RedirectIfApiAuthenticated::class,
            'api.permission' => EnsureApiPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Full page loads: no connection → offline screen with a retry link back to the page.
        $exceptions->render(fn (OfflineException $e, Request $request) => response()
            ->view('offline', ['retryUrl' => $request->fullUrl()], 503));

        $exceptions->render(fn (UnauthenticatedException $e) => redirect()
            ->route('login')
            ->with('status', __('auth.session_expired')));
    })->create();
