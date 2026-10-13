@props(['message' => null, 'offline' => false])
@if ($message)
    <x-alert :type="$offline ? 'warning' : 'error'" {{ $attributes->class('mb-4') }}>
        {{ $message }}
        @if ($offline)
            <button type="button" wire:click="$refresh" class="ms-2 font-bold underline">{{ __('app.retry') }}</button>
        @endif
    </x-alert>
@endif
