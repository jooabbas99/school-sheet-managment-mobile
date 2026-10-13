<?php

namespace App\Livewire\Students;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\GuardiansApi;
use App\Services\Api\StudentsApi;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    use HandlesApiErrors;

    public int $studentId;

    /** @var array<string, mixed>|null */
    public ?array $record = null;

    /** @var list<array<string, mixed>> */
    public array $siblings = [];

    public function mount(int $student): void
    {
        $this->studentId = $student;

        $this->record = $this->callApi(fn () => app(StudentsApi::class)->find($this->studentId));

        if ($this->record !== null) {
            $guardian = $this->callApi(fn () => app(GuardiansApi::class)->find($this->record['guardian']['id']), resetErrors: false);
            $this->siblings = array_values(array_filter(
                $guardian['students'] ?? [],
                fn (array $s) => $s['id'] !== $this->studentId,
            ));
        }
    }

    public function delete(StudentsApi $students): void
    {
        $this->callApi(function () use ($students) {
            $students->delete($this->studentId);
            session()->flash('status', __('students.deleted'));
            $this->redirectRoute('students.index', navigate: true);
        });
    }

    public function render(): View
    {
        return view('livewire.students.show')
            ->title($this->record['name'] ?? __('students.title'))
            ->layoutData(['back' => route('students.index')]);
    }
}
