<?php

namespace App\Livewire;

use App\Support\Navigation;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class More extends Component
{
    public function render(Navigation $navigation): View
    {
        return view('livewire.more', ['items' => $navigation->more()])->title(__('app.nav.more'));
    }
}
