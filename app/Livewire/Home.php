<?php

namespace App\Livewire;

use App\Support\CurrentUser;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Home extends Component
{
    public function render(CurrentUser $user): View
    {
        return view('livewire.home', ['user' => $user->data()])->title(__('app.nav.home'));
    }
}
