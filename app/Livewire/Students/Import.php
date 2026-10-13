<?php

namespace App\Livewire\Students;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\LookupsApi;
use App\Services\Api\StudentsApi;
use App\Services\Files\SharedFiles;
use App\Support\CurrentUser;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Import extends Component
{
    use HandlesApiErrors, WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $file = null;

    public string $institute_id = '';

    /** @var array{imported: int, failed: int, failed_rows: list<array{row: int, errors: list<string>}>}|null */
    public ?array $result = null;

    public function import(StudentsApi $students, CurrentUser $user): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'institute_id' => [$this->isSuperAdmin($user) ? 'required' : 'nullable'],
        ], [], [
            'file' => __('students.import_page.file'),
            'institute_id' => __('students.import_page.institute'),
        ]);

        $this->result = $this->callApi(fn () => $students->import(
            $this->file->getRealPath(),
            $this->file->getClientOriginalName(),
            $this->institute_id === '' ? null : (int) $this->institute_id,
        ));

        if ($this->result !== null) {
            app(LookupsApi::class)->forget();
            $this->reset('file');
        }
    }

    public function downloadTemplate(StudentsApi $students, SharedFiles $files): mixed
    {
        $content = $this->callApi(fn () => $students->importTemplate($this->institute_id === '' ? null : (int) $this->institute_id));

        if ($content === null) {
            return null;
        }

        $name = $files->store($content, __('students.import_page.template').'.xlsx');

        return $files->share($name, __('students.import_page.template'))
            ? null
            : $this->redirectRoute('files.show', ['name' => $name]);
    }

    private function isSuperAdmin(CurrentUser $user): bool
    {
        return ($user->data()['role']['value'] ?? null) === 'super_admin';
    }

    public function render(LookupsApi $lookups, CurrentUser $user): View
    {
        return view('livewire.students.import', [
            'institutes' => $this->isSuperAdmin($user) ? ($this->callApi(fn () => $lookups->instituteOptions(), resetErrors: false) ?? []) : [],
        ])
            ->title(__('students.import'))
            ->layoutData(['back' => route('students.index')]);
    }
}
