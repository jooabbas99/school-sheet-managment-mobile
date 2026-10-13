@php($tabs = app(\App\Support\Navigation::class)->tabs())
<x-layouts::base :title="$title ?? null">
    <header class="sticky top-0 z-10 bg-primary-700 px-4 pb-3 pt-4 text-white shadow safe-top">
        <h1 class="text-lg font-bold">{{ $title ?? __('app.name') }}</h1>
    </header>

    <main class="px-4 pb-28 pt-4">
        {{ $slot }}
    </main>

    <nav class="fixed inset-x-0 bottom-0 z-10 border-t border-slate-200 bg-white safe-bottom" aria-label="{{ __('app.name') }}">
        <ul class="flex">
            @foreach ($tabs as $tab)
                <li class="flex-1">
                    <a href="{{ route($tab['route']) }}" wire:navigate
                       @class(['flex flex-col items-center gap-1 py-2 text-xs', 'text-primary-700 font-bold' => request()->routeIs($tab['route']), 'text-slate-500' => ! request()->routeIs($tab['route'])])>
                        <x-icon :name="$tab['icon']" class="h-6 w-6" />
                        {{ $tab['label'] }}
                    </a>
                </li>
            @endforeach
            <li class="flex-1">
                <a href="{{ route('more') }}" wire:navigate
                   @class(['flex flex-col items-center gap-1 py-2 text-xs', 'text-primary-700 font-bold' => request()->routeIs('more'), 'text-slate-500' => ! request()->routeIs('more')])>
                    <x-icon name="menu" class="h-6 w-6" />
                    {{ __('app.nav.more') }}
                </a>
            </li>
        </ul>
    </nav>
</x-layouts::base>
