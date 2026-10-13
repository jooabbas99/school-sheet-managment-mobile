<div>
    <x-flash />
    <x-api-error :message="$errorMessage" :offline="$offline" />

    <div class="grid grid-cols-2 gap-2">
        <x-input type="search" wire:model.live.debounce.400ms="search" :placeholder="__('users.search')" aria-label="{{ __('users.search') }}" />
        <x-select wire:model.live="role" :options="__('users.roles')" :placeholder="__('users.all_roles')" aria-label="{{ __('users.fields.role') }}" />
    </div>

    <ul class="mt-3 space-y-2">
        @forelse ($users as $user)
            <li wire:key="user-{{ $user['id'] }}">
                <a href="{{ route('users.edit', $user['id']) }}" wire:navigate class="block rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <p class="min-w-0 truncate font-bold">{{ $user['name'] }}</p>
                        @unless ($user['is_active'])
                            <x-badge color="red">{{ __('users.inactive') }}</x-badge>
                        @endunless
                    </div>
                    <p class="truncate text-sm text-slate-500" dir="ltr" style="text-align: right">{{ $user['email'] }}</p>
                    <p class="text-xs text-slate-500">{{ $user['role']['label'] ?? '' }}@isset($user['institute']['name']) · {{ $user['institute']['name'] }}@endisset</p>
                </a>
            </li>
        @empty
            @unless ($errorMessage)
                <li class="py-10 text-center text-slate-500">{{ __('users.empty') }}</li>
            @endunless
        @endforelse
    </ul>

    @if ($hasMore)
        <x-button variant="secondary" class="mt-4 w-full" wire:click="loadMore">{{ __('students.load_more') }}</x-button>
    @endif

    <x-fab :href="route('users.create')" :label="__('users.new')" />
</div>
