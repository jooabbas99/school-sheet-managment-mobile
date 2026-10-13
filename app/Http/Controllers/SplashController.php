<?php

namespace App\Http\Controllers;

use App\Exceptions\UnauthenticatedException;
use App\Services\Api\AuthApi;
use App\Services\Auth\TokenStore;
use Illuminate\Http\RedirectResponse;

/**
 * App start: go to home when the saved token still works, otherwise to login.
 * Connection errors bubble up to the offline screen.
 */
class SplashController extends Controller
{
    public function __invoke(TokenStore $tokens, AuthApi $auth): RedirectResponse
    {
        if (! $tokens->get()) {
            return redirect()->route('login');
        }

        try {
            $auth->refresh();
        } catch (UnauthenticatedException) {
            return redirect()->route('login')->with('status', __('auth.session_expired'));
        }

        return redirect()->route('home');
    }
}
