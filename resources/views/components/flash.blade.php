@if (session('status'))
    <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
@endif
