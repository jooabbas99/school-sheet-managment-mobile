<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\UsersApi;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    use HandlesApiErrors;

    public string $search = '';

    public string $role = '';

    /** @var list<array<string, mixed>> */
    public array $users = [];

    public int $page = 1;

    public bool $hasMore = false;

    public function mount(): void
    {
        $this->load();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'role'], true)) {
            $this->load();
        }
    }

    public function loadMore(): void
    {
        $this->load($this->page + 1);
    }

    private function load(int $page = 1): void
    {
        $result = $this->callApi(fn () => app(UsersApi::class)->list(['search' => trim($this->search), 'role' => $this->role], $page));

        if ($result !== null) {
            $this->users = $page === 1 ? $result['data'] : [...$this->users, ...$result['data']];
            $this->page = $result['meta']['current_page'];
            $this->hasMore = $result['meta']['current_page'] < $result['meta']['last_page'];
        }
    }

    public function render(): View
    {
        return view('livewire.users.index')->title(__('users.title'))->layoutData(['back' => route('more')]);
    }
}
