<?php

namespace App\Http\Controllers;

use App\Services\Api\AuthApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request, AuthApi $auth): RedirectResponse
    {
        $auth->logout();
        $request->session()->regenerate();

        return redirect()->route('login')->with('status', __('auth.logged_out'));
    }
}
