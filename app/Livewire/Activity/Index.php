<?php

namespace App\Livewire\Activity;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\InsightsApi;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    use HandlesApiErrors;

    public string $subjectType = '';

    public string $event = '';

    /** @var list<array<string, mixed>> */
    public array $items = [];

    public int $page = 1;

    public bool $hasMore = false;

    public function mount(): void
    {
        $this->load();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['subjectType', 'event'], true)) {
            $this->load();
        }
    }

    public function loadMore(): void
    {
        $this->load($this->page + 1);
    }

    private function load(int $page = 1): void
    {
        $result = $this->callApi(fn () => app(InsightsApi::class)->activity(['subject_type' => $this->subjectType, 'event' => $this->event], $page));

        if ($result !== null) {
            $this->items = $page === 1 ? $result['data'] : [...$this->items, ...$result['data']];
            $this->page = $result['meta']['current_page'];
            $this->hasMore = $result['meta']['current_page'] < $result['meta']['last_page'];
        }
    }

    public function render(): View
    {
        return view('livewire.activity.index')->title(__('activity.title'))->layoutData(['back' => route('more')]);
    }
}
