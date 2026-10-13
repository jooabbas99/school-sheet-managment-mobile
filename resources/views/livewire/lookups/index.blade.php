<div class="space-y-4">
    <x-flash />
    <x-api-error :message="$errors->any() ? null : $errorMessage" :offline="$offline" />

    <div class="grid grid-cols-3 rounded-lg bg-slate-100 p-1 text-sm">
        @foreach (['institutes', 'grades', 'case_types'] as $key)
            <button type="button" wire:click="$set('tab', '{{ $key }}')"
                    @class(['rounded-md py-2', 'bg-white font-bold shadow-sm' => $tab === $key])>{{ __("lookups.tabs.$key") }}</button>
        @endforeach
    </div>

    @if ($editing !== null)
        <form wire:submit="save">
            <x-card class="space-y-3">
                @if ($tab === 'grades')
                    <x-field :label="__('lookups.fields.institute')" for="f_institute" error="form.institute_id">
                        <x-select id="f_institute" wire:model="form.institute_id" :options="$institutes" />
                    </x-field>
                @endif
                <x-field :label="__('lookups.fields.name')" for="f_name" error="form.name">
                    <x-input id="f_name" wire:model="form.name" />
                </x-field>
                @if ($tab === 'grades')
                    <x-field :label="__('lookups.fields.order')" for="f_order" error="form.order">
                        <x-input id="f_order" type="number" min="0" wire:model="form.order" />
                    </x-field>
                @elseif ($tab === 'case_types')
                    <x-field :label="__('lookups.fields.color')" for="f_color" error="form.color">
                        <input id="f_color" type="color" wire:model="form.color" class="h-11 w-full rounded-lg border border-slate-300">
                    </x-field>
                    <label class="flex items-center justify-between">
                        <span>{{ __('lookups.fields.is_active') }}</span>
                        <input type="checkbox" wire:model="form.is_active" class="h-6 w-6 rounded text-primary-700">
                    </label>
                @endif
                <div class="flex gap-2">
                    <x-button type="submit" class="flex-1" wire:loading.attr="disabled">{{ __('app.save') }}</x-button>
                    <x-button variant="ghost" class="flex-1" wire:click="cancel">{{ __('app.cancel') }}</x-button>
                </div>
            </x-card>
        </form>
    @else
        <x-button variant="secondary" class="w-full" wire:click="create">+ {{ __('lookups.add') }}</x-button>
    @endif

    <ul class="divide-y divide-slate-100 overflow-hidden rounded-xl bg-white shadow-sm">
        @forelse ($items as $item)
            <li wire:key="{{ $tab }}-{{ $item['id'] }}" class="flex items-center gap-3 px-4 py-3">
                @if ($tab === 'case_types')
                    <span class="h-4 w-4 shrink-0 rounded-full" style="background: {{ $item['color'] }}"></span>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="truncate">{{ $item['name'] }}</p>
                    @if ($tab === 'grades' && count($institutes) > 1)
                        <p class="truncate text-xs text-slate-500">{{ $institutes[$item['institute_id']] ?? '' }}</p>
                    @endif
                    @if ($tab === 'case_types' && ! $item['is_active'])
                        <x-badge>{{ __('lookups.inactive') }}</x-badge>
                    @endif
                </div>
                <button type="button" wire:click='edit(@json($item))' class="text-sm font-bold text-primary-700">{{ __('lookups.edit') }}</button>
                <div x-data="{ asking: false }">
                    <button type="button" x-show="! asking" x-on:click="asking = true" class="text-sm text-red-600">{{ __('lookups.delete') }}</button>
                    <button type="button" x-show="asking" x-cloak wire:click="delete({{ $item['id'] }})" class="text-sm font-bold text-red-600">{{ __('lookups.confirm_delete') }}</button>
                </div>
            </li>
        @empty
            <li class="px-4 py-8 text-center text-slate-500">{{ __('lookups.empty') }}</li>
        @endforelse
    </ul>
</div>
