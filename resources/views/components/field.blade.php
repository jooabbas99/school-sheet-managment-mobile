@props(['label', 'for' => null, 'error' => null, 'hint' => null])
<div {{ $attributes->class('space-y-1') }}>
    <label @if ($for) for="{{ $for }}" @endif class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @if ($error)
        @error($error) <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    @endif
</div>
