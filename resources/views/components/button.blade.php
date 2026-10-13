@props(['variant' => 'primary', 'href' => null])
@php
    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 font-bold disabled:opacity-60',
        'bg-primary-700 text-white' => $variant === 'primary',
        'bg-white text-primary-700 border border-primary-700' => $variant === 'secondary',
        'bg-red-600 text-white' => $variant === 'danger',
        'bg-transparent text-slate-600' => $variant === 'ghost',
    ];
@endphp
@if ($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class($classes) }}>{{ $slot }}</button>
@endif
