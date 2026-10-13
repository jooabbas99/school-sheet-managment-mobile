<?php

namespace App\Livewire\Reports;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\LookupsApi;
use App\Services\Api\ReportsApi;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    use HandlesApiErrors;

    public string $status = '';

    public string $caseTypeId = '';

    public string $from = '';

    public string $to = '';

    /** @var list<array<string, mixed>> */
    public array $reports = [];

    public int $page = 1;

    public bool $hasMore = false;

    public function mount(): void
    {
        $this->load();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'caseTypeId', 'from', 'to'], true)) {
            $this->load();
        }
    }

    public function loadMore(): void
    {
        $this->load($this->page + 1);
    }

    private function load(int $page = 1): void
    {
        $result = $this->callApi(fn () => app(ReportsApi::class)->list([
            'status' => $this->status,
            'case_type_id' => $this->caseTypeId,
            'from' => $this->from,
            'to' => $this->to,
        ], $page));

        if ($result === null) {
            return;
        }

        $this->reports = $page === 1 ? $result['data'] : [...$this->reports, ...$result['data']];
        $this->page = $result['meta']['current_page'];
        $this->hasMore = $result['meta']['current_page'] < $result['meta']['last_page'];
    }

    public function render(LookupsApi $lookups): View
    {
        return view('livewire.reports.index', [
            'caseTypes' => $this->callApi(fn () => $lookups->caseTypeOptions(activeOnly: false), resetErrors: false) ?? [],
        ])->title(__('reports.title'));
    }
}
