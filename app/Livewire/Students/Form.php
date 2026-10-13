<?php

namespace App\Livewire\Students;

use App\Enums\Gender;
use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\GuardiansApi;
use App\Services\Api\LookupsApi;
use App\Services\Api\StudentsApi;
use App\Support\ArabicDigits;
use App\Support\NationalId;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Add or edit a student. Every student has a guardian: pick an existing one or enter a new one.
 */
class Form extends Component
{
    use HandlesApiErrors;

    public ?int $studentId = null;

    public string $name = '';

    public string $national_id = '';

    public string $grade_id = '';

    public string $address = '';

    public string $birth_date = '';

    public string $gender = '';

    public string $status = 'active';

    public string $notes = '';

    /** existing | new */
    public string $guardianMode = 'new';

    public ?int $guardian_id = null;

    /** @var array<string, mixed>|null the picked guardian, for display */
    public ?array $pickedGuardian = null;

    public string $guardianSearch = '';

    /** @var list<array<string, mixed>> */
    public array $guardianResults = [];

    /** @var array{name: string, phone: string, national_id: string, address: string} */
    public array $guardian = ['name' => '', 'phone' => '', 'national_id' => '', 'address' => ''];

    public function mount(?int $student = null): void
    {
        if ($student === null) {
            return;
        }

        $data = $this->callApi(fn () => app(StudentsApi::class)->find($student));

        if ($data === null) {
            return;
        }

        $this->studentId = $student;
        $this->name = $data['name'];
        $this->national_id = $data['national_id'];
        $this->grade_id = (string) ($data['grade']['id'] ?? '');
        $this->address = $data['address'] ?? '';
        $this->birth_date = $data['birth_date'] ?? '';
        $this->gender = $data['gender']['value'] ?? '';
        $this->status = $data['status']['value'];
        $this->notes = $data['notes'] ?? '';
        $this->guardianMode = 'existing';
        $this->guardian_id = $data['guardian']['id'];
        $this->pickedGuardian = $data['guardian'];
    }

    /**
     * Fill birth date and gender as soon as the national ID is complete and valid.
     */
    public function updatedNationalId(): void
    {
        $this->national_id = (string) ArabicDigits::compact($this->national_id);

        if (NationalId::isValid($this->national_id)) {
            $this->birth_date = NationalId::birthDate($this->national_id)?->toDateString() ?? $this->birth_date;
            $this->gender = NationalId::gender($this->national_id)->value ?? $this->gender;
        }
    }

    public function updatedGuardianSearch(): void
    {
        $search = trim((string) ArabicDigits::normalize($this->guardianSearch));

        $this->guardianResults = mb_strlen($search) < 2
            ? []
            : ($this->callApi(fn () => app(GuardiansApi::class)->search($search)) ?? []);
    }

    public function pickGuardian(int $id): void
    {
        $picked = collect($this->guardianResults)->firstWhere('id', $id);

        if ($picked) {
            $this->guardian_id = $id;
            $this->pickedGuardian = $picked;
            $this->guardianResults = [];
            $this->guardianSearch = '';
        }
    }

    public function clearGuardian(): void
    {
        $this->guardian_id = null;
        $this->pickedGuardian = null;
    }

    public function save(StudentsApi $students): void
    {
        $this->resetErrorBag();

        $payload = [
            'name' => trim($this->name),
            'national_id' => (string) ArabicDigits::compact($this->national_id),
            'grade_id' => $this->grade_id === '' ? null : (int) $this->grade_id,
            'address' => trim($this->address),
            'birth_date' => $this->birth_date ?: null,
            'gender' => $this->gender ?: null,
            'status' => $this->status,
            'notes' => trim($this->notes) ?: null,
        ];

        if ($this->guardianMode === 'existing') {
            $payload['guardian_id'] = $this->guardian_id;
        } else {
            $payload['guardian'] = [
                'name' => trim($this->guardian['name']),
                'phone' => (string) ArabicDigits::compact($this->guardian['phone']),
                'national_id' => ArabicDigits::compact($this->guardian['national_id']) ?: null,
                'address' => trim($this->guardian['address']) ?: null,
            ];
        }

        $saved = $this->callApi(fn () => $this->studentId
            ? $students->update($this->studentId, $payload)
            : $students->create($payload));

        if ($saved !== null) {
            session()->flash('status', __('students.saved'));
            $this->redirectRoute('students.show', ['student' => $saved['id']], navigate: true);
        }
    }

    public function render(LookupsApi $lookups): View
    {
        $back = $this->studentId ? route('students.show', $this->studentId) : route('students.index');

        return view('livewire.students.form', [
            'grades' => $this->callApi(fn () => $lookups->gradeOptions(), resetErrors: false) ?? [],
            'genders' => collect(Gender::cases())->mapWithKeys(fn (Gender $g) => [$g->value => $g->label()])->all(),
        ])
            ->title(__($this->studentId ? 'students.edit' : 'students.add'))
            ->layoutData(['back' => $back]);
    }
}
