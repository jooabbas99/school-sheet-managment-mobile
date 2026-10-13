<div>
    <x-api-error :message="$errorMessage" :offline="$offline" />

    <div class="grid grid-cols-2 gap-2">
        <x-select wire:model.live="subjectType" :options="__('activity.types')" :placeholder="__('activity.all_types')" aria-label="{{ __('activity.all_types') }}" />
        <x-select wire:model.live="event" :options="__('activity.events')" :placeholder="__('activity.all_events')" aria-label="{{ __('activity.all_events') }}" />
    </div>

    <ul class="mt-3 space-y-2">
        @forelse ($items as $item)
            <li wire:key="act-{{ $item['id'] }}" x-data="{ open: false }" class="rounded-xl bg-white p-3 shadow-sm">
                <button type="button" x-on:click="open = ! open" class="w-full text-right">
                    <p class="break-words text-sm">{{ $item['description'] }}</p>
                    <p class="mt-1 text-xs text-slate-500" dir="ltr" style="text-align: right">{{ \Illuminate\Support\Carbon::parse($item['created_at'])->format('Y-m-d H:i') }}</p>
                </button>
                @php($changes = array_keys(array_merge((array) $item['changes']['old'], (array) $item['changes']['attributes'])))
                @if ($changes)
                    <dl x-show="open" x-cloak class="mt-2 space-y-1 border-t border-slate-100 pt-2 text-xs">
                        @foreach ($changes as $field)
                            <div class="break-words">
                                <dt class="inline font-bold">{{ $field }}:</dt>
                                @isset($item['changes']['old'][$field])
                                    <dd class="inline text-red-600 line-through">{{ is_scalar($item['changes']['old'][$field]) ? $item['changes']['old'][$field] : json_encode($item['changes']['old'][$field], JSON_UNESCAPED_UNICODE) }}</dd>
                                @endisset
                                <dd class="inline text-emerald-700">{{ is_scalar($item['changes']['attributes'][$field] ?? null) ? $item['changes']['attributes'][$field] : json_encode($item['changes']['attributes'][$field] ?? null, JSON_UNESCAPED_UNICODE) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </li>
        @empty
            @unless ($errorMessage)
                <li class="py-10 text-center text-slate-500">{{ __('activity.empty') }}</li>
            @endunless
        @endforelse
    </ul>

    @if ($hasMore)
        <x-button variant="secondary" class="mt-4 w-full" wire:click="loadMore">{{ __('activity.load_more') }}</x-button>
    @endif
</div>
