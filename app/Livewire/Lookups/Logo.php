<?php

namespace App\Livewire\Lookups;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\LookupsAdminApi;
use App\Services\Api\LookupsApi;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Institute logo (admins: their own institute; super admins: any institute).
 */
class Logo extends Component
{
    use HandlesApiErrors, WithFileUploads;

    public string $instituteId = '';

    /** @var TemporaryUploadedFile|null */
    public $logo = null;

    public function mount(LookupsApi $lookups): void
    {
        $this->instituteId = (string) (array_key_first($this->callApi(fn () => $lookups->instituteOptions()) ?? []) ?? '');
    }

    public function upload(LookupsAdminApi $api): void
    {
        $this->validate(['logo' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:1024']], [], ['logo' => __('lookups.logo.file')]);

        $done = $this->callApi(function () use ($api) {
            $api->uploadLogo((int) $this->instituteId, $this->logo->getRealPath(), $this->logo->getClientOriginalName());

            return true;
        });

        if ($done) {
            $this->reset('logo');
            session()->flash('status', __('lookups.logo.uploaded'));
        }
    }

    public function remove(LookupsAdminApi $api): void
    {
        $done = $this->callApi(function () use ($api) {
            $api->deleteLogo((int) $this->instituteId);

            return true;
        });

        if ($done) {
            session()->flash('status', __('lookups.logo.removed'));
        }
    }

    public function render(LookupsApi $lookups, LookupsAdminApi $api): View
    {
        $institutes = $this->callApi(fn () => $lookups->all()['institutes'], resetErrors: false) ?? [];
        $current = collect($institutes)->firstWhere('id', (int) $this->instituteId);
        $image = null;

        if ($current['has_logo'] ?? false) {
            $bytes = $this->callApi(fn () => $api->logo((int) $this->instituteId), resetErrors: false);
            $image = $bytes ? 'data:'.(new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes).';base64,'.base64_encode($bytes) : null;
        }

        return view('livewire.lookups.logo', [
            'institutes' => array_column($institutes, 'name', 'id'),
            'image' => $image,
        ])->title(__('lookups.logo.title'))->layoutData(['back' => route('more')]);
    }
}
