<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Placeholder for sections built in phases 6 and 7.
 */
class ComingSoon extends Component
{
    public string $titleKey = 'app.nav.home';

    public function mount(string $titleKey): void
    {
        $this->titleKey = $titleKey;
    }

    public function render(): View
    {
        return view('livewire.coming-soon')->title(__($this->titleKey));
    }
}
