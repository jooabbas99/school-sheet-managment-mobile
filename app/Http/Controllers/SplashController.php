<?php

namespace App\Http\Controllers;

use App\Exceptions\UnauthenticatedException;
use App\Services\Api\AuthApi;
use App\Services\Api\LookupsApi;
use App\Services\Auth\TokenStore;
use App\Services\Files\SharedFiles;
use Illuminate\Http\RedirectResponse;

/**
 * App start: go to home when the saved token still works, otherwise to login.
 * Connection errors bubble up to the offline screen.
 */
class SplashController extends Controller
{
    public function __invoke(TokenStore $tokens, AuthApi $auth, SharedFiles $files, LookupsApi $lookups): RedirectResponse
    {
        // Fresh institutes / grades / case types on every app start.
        $lookups->forget();

        // Housekeeping on every start: shared report files don't need to stay on the phone.
        $files->prune(config('api.keep_downloads_days'));

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
