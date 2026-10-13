<?php

namespace App\Livewire\Students;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\LookupsApi;
use App\Services\Api\StudentsApi;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    use HandlesApiErrors;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $gradeId = '';

    #[Url(except: '')]
    public string $status = '';

    /** @var list<array<string, mixed>> */
    public array $students = [];

    public int $page = 1;

    public bool $hasMore = false;

    public int $total = 0;

    public function mount(): void
    {
        $this->load();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'gradeId', 'status'], true)) {
            $this->load();
        }
    }

    public function loadMore(): void
    {
        $this->load($this->page + 1);
    }

    public function refreshList(): void
    {
        app(LookupsApi::class)->forget();
        $this->load();
    }

    private function load(int $page = 1): void
    {
        $result = $this->callApi(fn () => app(StudentsApi::class)->list([
            'search' => trim($this->search),
            'grade_id' => $this->gradeId,
            'status' => $this->status,
        ], $page));

        if ($result === null) {
            return;
        }

        $this->students = $page === 1 ? $result['data'] : [...$this->students, ...$result['data']];
        $this->page = $result['meta']['current_page'];
        $this->hasMore = $result['meta']['current_page'] < $result['meta']['last_page'];
        $this->total = $result['meta']['total'];
    }

    public function render(LookupsApi $lookups): View
    {
        $grades = $this->callApi(fn () => $lookups->gradeOptions(), resetErrors: false) ?? [];

        return view('livewire.students.index', ['grades' => $grades])->title(__('students.title'));
    }
}
