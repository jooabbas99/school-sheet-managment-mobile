<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthenticatedException;
use App\Services\Api\AuthApi;
use App\Services\Auth\TokenStore;
use App\Support\CurrentUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires a token on the device. Loads the user from GET /me when the session doesn't have it yet.
 */
class EnsureApiAuthenticated
{
    public function __construct(
        private readonly TokenStore $tokens,
        private readonly CurrentUser $currentUser,
        private readonly AuthApi $auth,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tokens->get()) {
            $this->currentUser->forget();

            return redirect()->route('login');
        }

        if (! $this->currentUser->check()) {
            try {
                $this->auth->refresh();
            } catch (UnauthenticatedException) {
                return redirect()->route('login')->with('status', __('auth.session_expired'));
            }
        }

        return $next($request);
    }
}
