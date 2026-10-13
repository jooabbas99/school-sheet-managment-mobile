@props(['status'])
{{-- Report or student status from the API: {value, label} --}}
<x-badge :color="match ($status['value'] ?? '') { 'ready', 'active' => 'green', 'failed' => 'red', 'pending' => 'amber', default => 'slate' }">
    {{ $status['label'] ?? '' }}
</x-badge>
