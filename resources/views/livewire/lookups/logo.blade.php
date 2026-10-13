<div class="space-y-4">
    <x-flash />
    <x-api-error :message="$errors->any() ? null : $errorMessage" :offline="$offline" />

    <x-card class="space-y-4">
        <p class="text-sm text-slate-600">{{ __('lookups.logo.intro') }}</p>

        @if (count($institutes) > 1)
            <x-field :label="__('lookups.logo.institute')" for="institute">
                <x-select id="institute" wire:model.live="instituteId" :options="$institutes" />
            </x-field>
        @endif

        <div>
            <p class="mb-2 text-sm font-medium">{{ __('lookups.logo.current') }}</p>
            @if ($image)
                <img src="{{ $image }}" alt="{{ __('lookups.logo.title') }}" class="h-24 w-24 rounded-lg border border-slate-200 object-contain">
            @else
                <p class="text-sm text-slate-500">{{ __('lookups.logo.none') }}</p>
            @endif
        </div>
    </x-card>

    <form wire:submit="upload">
        <x-card class="space-y-4">
            <x-field :label="__('lookups.logo.file')" for="logo" error="logo">
                <input id="logo" type="file" wire:model="logo" accept="image/png,image/jpeg"
                       class="w-full text-sm file:me-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:font-bold file:text-primary-700">
            </x-field>
            <x-button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="upload,logo">{{ __('lookups.logo.upload') }}</x-button>
            @if ($image)
                <x-confirm-button action="remove" :label="__('lookups.logo.remove')" :confirm="__('lookups.confirm_delete')" />
            @endif
        </x-card>
    </form>
</div>
