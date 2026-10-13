<?php

namespace App\Http\Middleware;

use App\Services\Auth\TokenStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfApiAuthenticated
{
    public function __construct(private readonly TokenStore $tokens) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $this->tokens->get() ? redirect()->route('home') : $next($request);
    }
}
