<?php

namespace App\Livewire\Reports;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\ReportsApi;
use App\Services\Files\SharedFiles;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * A report: status (polled while the file is being generated), share/download, regenerate, duplicate, delete.
 */
class Show extends Component
{
    use HandlesApiErrors;

    public int $reportId;

    /** @var array<string, mixed>|null */
    public ?array $record = null;

    /** @var list<array<string, mixed>> */
    public array $students = [];

    public int $studentsPage = 1;

    public bool $hasMoreStudents = false;

    public function mount(int $report): void
    {
        $this->reportId = $report;
        $this->refreshStatus();
        $this->loadStudents();
    }

    /**
     * Called by wire:poll while the report is pending.
     */
    public function refreshStatus(): void
    {
        $this->record = $this->callApi(fn () => app(ReportsApi::class)->find($this->reportId)) ?? $this->record;
    }

    public function loadStudents(int $page = 1): void
    {
        $result = $this->callApi(fn () => app(ReportsApi::class)->students($this->reportId, $page), resetErrors: false);

        if ($result !== null) {
            $this->students = $page === 1 ? $result['data'] : [...$this->students, ...$result['data']];
            $this->studentsPage = $result['meta']['current_page'];
            $this->hasMoreStudents = $result['meta']['current_page'] < $result['meta']['last_page'];
        }
    }

    public function loadMoreStudents(): void
    {
        $this->loadStudents($this->studentsPage + 1);
    }

    /**
     * On the phone: download through the signed link and open the share sheet with the .xlsx.
     * Under Jump: share the signed link. In a browser: download the file.
     */
    public function share(ReportsApi $reports, SharedFiles $files): mixed
    {
        $link = $this->callApi(fn () => $reports->link($this->reportId));

        if ($link === null) {
            return null;
        }

        $title = $this->record['title'] ?? $link['file_name'];

        if ($files->shareLink($link['url'], $title)) {
            return null;
        }

        $content = $this->callApi(fn () => $reports->fetch($link['url']));

        if ($content === null) {
            return null;
        }

        $name = $files->store($content, $link['file_name']);

        return $files->share($name, $title) ? null : $this->redirectRoute('files.show', ['name' => $name]);
    }

    public function regenerate(ReportsApi $reports): void
    {
        $updated = $this->callApi(fn () => $reports->regenerate($this->reportId));

        if ($updated !== null) {
            $this->record = $updated;
            session()->flash('status', __('reports.regenerated'));
        }
    }

    public function duplicate(ReportsApi $reports): void
    {
        $copy = $this->callApi(fn () => $reports->duplicate($this->reportId));

        if ($copy !== null) {
            session()->flash('status', __('reports.duplicated'));
            $this->redirectRoute('reports.show', ['report' => $copy['id']], navigate: true);
        }
    }

    public function delete(ReportsApi $reports): void
    {
        $this->callApi(function () use ($reports) {
            $reports->delete($this->reportId);
            session()->flash('status', __('reports.deleted'));
            $this->redirectRoute('reports.index', navigate: true);
        });
    }

    public function render(): View
    {
        return view('livewire.reports.show')
            ->title($this->record['title'] ?? __('reports.title'))
            ->layoutData(['back' => route('reports.index')]);
    }
}
