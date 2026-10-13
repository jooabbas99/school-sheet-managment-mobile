<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\InsightsApi;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    use HandlesApiErrors;

    /** @var array<string, mixed>|null */
    public ?array $data = null;

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->data = $this->callApi(fn () => app(InsightsApi::class)->dashboard()) ?? $this->data;
    }

    public function render(): View
    {
        return view('livewire.dashboard')->title(__('dashboard.title'))->layoutData(['back' => route('more')]);
    }
}
