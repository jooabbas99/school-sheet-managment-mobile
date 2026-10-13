@props(['options' => [], 'placeholder' => null])
<select {{ $attributes->class('w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-primary-600 focus:ring-primary-600') }}>
    @if ($placeholder !== null)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($options as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
    @endforeach
</select>
