<?php

namespace App\Livewire\Reports;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\LookupsApi;
use App\Services\Api\ReportsApi;
use App\Services\Api\StudentsApi;
use App\Services\Api\TemplatesApi;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * New report: template → case type → students (filter, pick, or select all filtered) → review → generate.
 */
class Wizard extends Component
{
    use HandlesApiErrors;

    /** Same limit as the backend (reports.max_students). */
    public const MAX_STUDENTS = 5000;

    public const STEPS = 4;

    public int $step = 1;

    /** @var list<array<string, mixed>> */
    public array $templates = [];

    public ?int $templateId = null;

    public string $caseTypeId = '';

    public string $search = '';

    public string $gradeId = '';

    public string $status = 'active';

    /** @var list<array<string, mixed>> */
    public array $students = [];

    public int $page = 1;

    public bool $hasMore = false;

    public int $total = 0;

    /** @var list<int> */
    public array $selected = [];

    /** @var array<int, string> id => name, for the review step */
    public array $names = [];

    public bool $truncated = false;

    public string $title = '';

    public function mount(): void
    {
        $this->templates = $this->callApi(fn () => app(TemplatesApi::class)->list()) ?? [];
        $default = collect($this->templates)->firstWhere('is_default', true) ?? ($this->templates[0] ?? null);
        $this->templateId = $default['id'] ?? null;
    }

    public function next(): void
    {
        $this->resetErrorBag();

        if ($this->step === 1 && $this->templateId === null) {
            $this->addError('templateId', __('reports.wizard.no_templates'));

            return;
        }

        if ($this->step === 3 && $this->selected === []) {
            $this->addError('selected', __('reports.wizard.select_some'));

            return;
        }

        $this->step = min($this->step + 1, self::STEPS);

        if ($this->step === 3 && $this->students === []) {
            $this->loadStudents();
        }
    }

    public function back(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'gradeId', 'status'], true)) {
            $this->loadStudents();
        }
    }

    public function loadMore(): void
    {
        $this->loadStudents($this->page + 1);
    }

    public function toggle(int $id): void
    {
        if (in_array($id, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } elseif (count($this->selected) < self::MAX_STUDENTS) {
            $this->selected[] = $id;
        }
    }

    /**
     * Add every student matching the current filters (not only the loaded page).
     */
    public function selectAllFiltered(StudentsApi $students): void
    {
        $result = $this->callApi(fn () => $students->ids($this->filters()));

        if ($result === null) {
            return;
        }

        $merged = array_values(array_unique([...$this->selected, ...$result['ids']]));
        $this->truncated = $result['truncated'] || count($merged) > self::MAX_STUDENTS;
        $this->selected = array_slice($merged, 0, self::MAX_STUDENTS);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->truncated = false;
    }

    public function generate(ReportsApi $reports): void
    {
        $report = $this->callApi(fn () => $reports->create([
            'template_id' => $this->templateId,
            'case_type_id' => $this->caseTypeId === '' ? null : (int) $this->caseTypeId,
            'student_ids' => $this->selected,
            'title' => trim($this->title) ?: null,
        ]));

        if ($report !== null) {
            $this->redirectRoute('reports.show', ['report' => $report['id']], navigate: true);
        }
    }

    private function loadStudents(int $page = 1): void
    {
        $result = $this->callApi(fn () => app(StudentsApi::class)->list($this->filters(), $page));

        if ($result === null) {
            return;
        }

        $this->students = $page === 1 ? $result['data'] : [...$this->students, ...$result['data']];
        $this->page = $result['meta']['current_page'];
        $this->hasMore = $result['meta']['current_page'] < $result['meta']['last_page'];
        $this->total = $result['meta']['total'];

        foreach ($result['data'] as $student) {
            $this->names[$student['id']] = $student['name'];
        }
    }

    /**
     * @return array<string, string>
     */
    private function filters(): array
    {
        return ['search' => trim($this->search), 'grade_id' => $this->gradeId, 'status' => $this->status];
    }

    public function render(LookupsApi $lookups): View
    {
        return view('livewire.reports.wizard', [
            'caseTypes' => $this->callApi(fn () => $lookups->caseTypeOptions(), resetErrors: false) ?? [],
            'grades' => $this->step === 3 ? ($this->callApi(fn () => $lookups->gradeOptions(), resetErrors: false) ?? []) : [],
            'template' => collect($this->templates)->firstWhere('id', $this->templateId),
        ])
            ->title(__('reports.wizard.title'))
            ->layoutData(['back' => route('reports.index')]);
    }
}
