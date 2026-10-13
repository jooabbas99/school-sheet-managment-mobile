<div class="space-y-4">
    @if ($items)
        <ul class="divide-y divide-slate-100 overflow-hidden rounded-xl bg-white shadow-sm">
            @foreach ($items as $item)
                <li>
                    <a href="{{ route($item['route']) }}" wire:navigate class="flex items-center gap-3 px-4 py-4">
                        <x-icon :name="$item['icon']" class="h-6 w-6 text-primary-700" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex w-full items-center gap-3 rounded-xl bg-white px-4 py-4 text-red-600 shadow-sm">
            <x-icon name="logout" class="h-6 w-6" />
            {{ __('app.nav.logout') }}
        </button>
    </form>
</div>
