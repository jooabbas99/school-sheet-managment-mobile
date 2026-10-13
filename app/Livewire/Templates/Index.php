<?php

namespace App\Livewire\Templates;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\TemplatesApi;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    use HandlesApiErrors;

    /** @var list<array<string, mixed>> */
    public array $templates = [];

    public function mount(): void
    {
        $this->load();
    }

    public function makeDefault(int $id, TemplatesApi $api): void
    {
        if ($this->callApi(fn () => $api->makeDefault($id)) !== null) {
            session()->flash('status', __('templates.defaulted'));
            $this->load();
        }
    }

    public function duplicate(int $id, TemplatesApi $api): void
    {
        $copy = $this->callApi(fn () => $api->duplicate($id));

        if ($copy !== null) {
            session()->flash('status', __('templates.duplicated'));
            $this->redirectRoute('templates.edit', ['template' => $copy['id']], navigate: true);
        }
    }

    public function delete(int $id, TemplatesApi $api): void
    {
        $deleted = $this->callApi(function () use ($api, $id) {
            $api->delete($id);

            return true;
        });

        if ($deleted) {
            session()->flash('status', __('templates.deleted'));
            $this->load();
        }
    }

    private function load(): void
    {
        $this->templates = $this->callApi(fn () => app(TemplatesApi::class)->list(), resetErrors: false) ?? $this->templates;
    }

    public function render(): View
    {
        return view('livewire.templates.index')->title(__('templates.title'));
    }
}
