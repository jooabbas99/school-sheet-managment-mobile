<?php

namespace App\Livewire\Lookups;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\LookupsAdminApi;
use App\Services\Api\LookupsApi;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Institutes, grades and case types (lookups.manage): list, add, edit, delete.
 */
class Index extends Component
{
    use HandlesApiErrors;

    #[Url]
    public string $tab = 'institutes';

    /** Id being edited, 0 for a new item, null when the form is closed. */
    public ?int $editing = null;

    /** @var array<string, mixed> */
    public array $form = [];

    public function updatedTab(): void
    {
        $this->cancel();
    }

    public function create(): void
    {
        $this->resetErrorBag();
        $this->editing = 0;
        $this->form = match ($this->tab) {
            'grades' => ['institute_id' => (string) array_key_first(app(LookupsApi::class)->instituteOptions()), 'name' => '', 'order' => 1],
            'case_types' => ['name' => '', 'color' => '#2563EB', 'is_active' => true],
            default => ['name' => ''],
        };
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function edit(array $item): void
    {
        $this->resetErrorBag();
        $this->editing = (int) $item['id'];
        $this->form = match ($this->tab) {
            'grades' => ['institute_id' => (string) $item['institute_id'], 'name' => $item['name'], 'order' => $item['order']],
            'case_types' => ['name' => $item['name'], 'color' => $item['color'], 'is_active' => (bool) $item['is_active']],
            default => ['name' => $item['name']],
        };
    }

    public function cancel(): void
    {
        $this->editing = null;
        $this->form = [];
        $this->resetErrorBag();
    }

    public function save(LookupsAdminApi $api): void
    {
        $this->resetErrorBag();
        $data = $this->form;

        if ($this->tab === 'grades') {
            $data['institute_id'] = (int) $data['institute_id'];
            $data['order'] = (int) $data['order'];
        }
        if ($this->tab === 'case_types') {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $saved = $this->callApi(fn () => $api->save($this->tab, $this->editing ?: null, $data));

        if ($saved !== null) {
            session()->flash('status', __('lookups.saved'));
            $this->cancel();
        }
    }

    public function delete(int $id, LookupsAdminApi $api): void
    {
        $deleted = $this->callApi(function () use ($api, $id) {
            $api->delete($this->tab, $id);

            return true;
        });

        if ($deleted) {
            session()->flash('status', __('lookups.deleted'));
        }
    }

    /**
     * Field errors come back as "name", "color"...; the form fields are "form.name".
     */
    public function addError($name, $message)
    {
        return parent::addError(str_starts_with($name, 'form.') ? $name : 'form.'.$name, $message);
    }

    public function render(LookupsApi $lookups): View
    {
        $lookups->forget();
        $data = $this->callApi(fn () => $lookups->all(), resetErrors: false) ?? ['institutes' => [], 'grades' => [], 'case_types' => []];

        return view('livewire.lookups.index', [
            'items' => $data[$this->tab] ?? [],
            'institutes' => array_column($data['institutes'], 'name', 'id'),
        ])->title(__('lookups.title'));
    }
}
