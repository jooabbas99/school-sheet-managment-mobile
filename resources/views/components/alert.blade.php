@props(['type' => 'error'])
<div role="alert" {{ $attributes->class([
    'rounded-lg px-4 py-3 text-sm',
    'bg-red-50 text-red-700 border border-red-200' => $type === 'error',
    'bg-emerald-50 text-emerald-700 border border-emerald-200' => $type === 'success',
    'bg-amber-50 text-amber-800 border border-amber-200' => $type === 'warning',
]) }}>
    {{ $slot }}
</div>
