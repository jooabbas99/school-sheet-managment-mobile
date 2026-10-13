<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\AuthApi;
use App\Support\ArabicDigits;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::guest')]
class Login extends Component
{
    use HandlesApiErrors;

    public string $email = '';

    public string $password = '';

    public function login(AuthApi $auth): void
    {
        $this->email = strtolower(trim(ArabicDigits::normalize($this->email)));

        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => __('auth.email'),
            'password' => __('auth.password'),
        ]);

        $this->callApi(function () use ($auth) {
            $auth->login($this->email, $this->password);
            $this->redirectRoute('home');
        });

        $this->password = '';
    }

    public function render(): View
    {
        return view('livewire.auth.login')->title(__('auth.title'));
    }
}
