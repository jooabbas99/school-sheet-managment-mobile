<div class="space-y-3">
    <x-flash />
    <x-api-error :message="$errorMessage" :offline="$offline" />

    @forelse ($templates as $template)
        <x-card wire:key="tpl-{{ $template['id'] }}" class="space-y-3">
            <div class="flex items-start justify-between gap-2">
                <a href="{{ route('templates.edit', $template['id']) }}" wire:navigate class="min-w-0">
                    <p class="font-bold">{{ $template['name'] }}</p>
                    <p class="text-sm text-slate-500">{{ __('templates.columns_count', ['count' => $template['columns_count'] ?? 0]) }}</p>
                </a>
                @if ($template['is_default'])
                    <x-badge color="blue">{{ __('templates.default') }}</x-badge>
                @endif
            </div>
            <div class="flex flex-wrap gap-2 text-sm">
                <x-button variant="secondary" class="!px-3 !py-1.5" :href="route('templates.edit', $template['id'])">{{ __('app.edit') }}</x-button>
                <x-button variant="secondary" class="!px-3 !py-1.5" wire:click="duplicate({{ $template['id'] }})">{{ __('templates.duplicate') }}</x-button>
                @unless ($template['is_default'])
                    <x-button variant="secondary" class="!px-3 !py-1.5" wire:click="makeDefault({{ $template['id'] }})">{{ __('templates.make_default') }}</x-button>
                    <div x-data="{ asking: false }" class="inline-flex gap-1">
                        <x-button variant="ghost" class="!px-3 !py-1.5 text-red-600" x-show="! asking" x-on:click="asking = true">{{ __('templates.delete') }}</x-button>
                        <x-button variant="danger" class="!px-3 !py-1.5" x-show="asking" x-cloak wire:click="delete({{ $template['id'] }})">{{ __('templates.confirm_delete') }}</x-button>
                    </div>
                @endunless
            </div>
        </x-card>
    @empty
        @unless ($errorMessage)
            <p class="py-10 text-center text-slate-500">{{ __('templates.empty') }}</p>
        @endunless
    @endforelse

    <x-fab :href="route('templates.create')" :label="__('templates.new')" />
</div>
