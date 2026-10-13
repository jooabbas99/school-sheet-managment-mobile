@props(['action', 'label', 'confirm'])
{{-- Two-tap confirmation without a browser dialog (dialogs block the app WebView). --}}
<div x-data="{ asking: false }" {{ $attributes }}>
    <x-button variant="danger" class="w-full" x-show="! asking" x-on:click="asking = true">{{ $label }}</x-button>
    <div x-show="asking" x-cloak class="flex gap-2">
        <x-button variant="danger" class="flex-1" wire:click="{{ $action }}" wire:loading.attr="disabled">{{ $confirm }}</x-button>
        <x-button variant="ghost" class="flex-1" x-on:click="asking = false">{{ __('app.cancel') }}</x-button>
    </div>
</div>
