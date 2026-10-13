<x-layouts::guest :title="__('errors.offline_title')">
    <div class="mx-auto max-w-sm text-center">
        <x-icon name="wifi-off" class="mx-auto mb-4 h-16 w-16 text-slate-400" />
        <h1 class="text-xl font-bold">{{ __('errors.offline_title') }}</h1>
        <p class="mt-2 text-slate-500">{{ __('errors.offline') }}</p>
        <a href="{{ $retryUrl ?? route('splash') }}" class="mt-6 inline-block rounded-lg bg-primary-700 px-6 py-3 font-bold text-white">
            {{ __('app.retry') }}
        </a>
    </div>
</x-layouts::guest>
