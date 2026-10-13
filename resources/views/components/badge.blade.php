@props(['color' => 'slate'])
@php
    $styles = [
        'green' => 'bg-emerald-50 text-emerald-700',
        'red' => 'bg-red-50 text-red-700',
        'amber' => 'bg-amber-50 text-amber-800',
        'blue' => 'bg-primary-50 text-primary-700',
        'slate' => 'bg-slate-100 text-slate-600',
    ];
@endphp
<span {{ $attributes->class(['inline-block rounded-full px-2.5 py-0.5 text-xs font-medium', $styles[$color] ?? $styles['slate']]) }}>{{ $slot }}</span>
