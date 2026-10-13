<div>
    <x-flash />
    <x-api-error :message="$errorMessage" :offline="$offline" />

    <div class="space-y-2">
        <x-input type="search" wire:model.live.debounce.400ms="search" :placeholder="__('students.search')" aria-label="{{ __('students.search') }}" />
        <div class="grid grid-cols-2 gap-2">
            <x-select wire:model.live="gradeId" :options="$grades" :placeholder="__('students.all_grades')" aria-label="{{ __('students.fields.grade') }}" />
            <x-select wire:model.live="status" :options="['active' => __('enums.student_status.active'), 'inactive' => __('enums.student_status.inactive')]"
                      :placeholder="__('students.all_statuses')" aria-label="{{ __('students.fields.status') }}" />
        </div>
    </div>

    <div class="mt-3 flex items-center justify-between text-sm text-slate-500">
        <span>{{ __('students.count', ['count' => $total]) }}</span>
        <div class="flex gap-3">
            @if (app(\App\Support\CurrentUser::class)->can('students.import'))
                <a href="{{ route('students.import') }}" wire:navigate class="font-bold text-primary-700">{{ __('students.import') }}</a>
            @endif
            <button type="button" wire:click="refreshList" class="font-bold text-primary-700">{{ __('students.refresh') }}</button>
        </div>
    </div>

    <ul class="mt-3 space-y-2" wire:loading.class="opacity-60">
        @forelse ($students as $student)
            <li wire:key="student-{{ $student['id'] }}">
                <a href="{{ route('students.show', $student['id']) }}" wire:navigate class="block rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <p class="min-w-0 font-bold">{{ $student['name'] }}</p>
                        @if ($student['status']['value'] !== 'active')
                            <x-status-badge :status="$student['status']" />
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-slate-500">{{ $student['grade']['name'] ?? '' }}</p>
                    <p class="mt-1 flex justify-between gap-2 text-sm text-slate-500">
                        <span class="min-w-0 truncate">{{ $student['guardian']['name'] ?? '' }}</span>
                        <span dir="ltr" class="shrink-0">{{ $student['guardian']['phone'] ?? '' }}</span>
                    </p>
                </a>
            </li>
        @empty
            @unless ($errorMessage)
                <li class="py-10 text-center text-slate-500">{{ __('students.empty') }}</li>
            @endunless
        @endforelse
    </ul>

    @if ($hasMore)
        <x-button variant="secondary" class="mt-4 w-full" wire:click="loadMore" wire:loading.attr="disabled">{{ __('students.load_more') }}</x-button>
    @endif

    @if (app(\App\Support\CurrentUser::class)->can('students.create'))
        <x-fab :href="route('students.create')" :label="__('students.add')" />
    @endif
</div>
