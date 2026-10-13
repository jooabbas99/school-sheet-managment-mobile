@props(['ltr' => false])
<input {{ $attributes->merge(['type' => 'text'])->class([
    'w-full min-w-0 rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-primary-600 focus:ring-primary-600',
    'text-left' => $ltr,
]) }} @if ($ltr) dir="ltr" @endif>
