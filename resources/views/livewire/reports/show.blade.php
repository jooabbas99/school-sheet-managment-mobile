@php($user = app(\App\Support\CurrentUser::class))
<div class="space-y-4" @if (($record['status']['value'] ?? null) === 'pending') wire:poll.2s="refreshStatus" @endif>
    <x-flash />
    <x-api-error :message="$errorMessage" :offline="$offline" />

    @if ($record)
        <x-card>
            <div class="flex items-start justify-between gap-2">
                <h2 class="font-bold">{{ $record['title'] }}</h2>
                <x-status-badge :status="$record['status']" />
            </div>
            <dl class="mt-3 grid grid-cols-3 gap-x-2 gap-y-2 text-sm [&>dd]:min-w-0 [&>dd]:break-words">
                <dt class="text-slate-500">{{ __('reports.template') }}</dt><dd class="col-span-2">{{ $record['template']['name'] ?? __('app.none') }}</dd>
                <dt class="text-slate-500">{{ __('reports.case_type') }}</dt><dd class="col-span-2">{{ $record['case_type']['name'] ?? __('reports.no_case_type') }}</dd>
                <dt class="text-slate-500">{{ __('students.title') }}</dt><dd class="col-span-2">{{ $record['students_count'] }}</dd>
            </dl>

            @if ($record['status']['value'] === 'pending')
                <x-alert type="warning" class="mt-3">{{ __('reports.pending') }}</x-alert>
            @elseif ($record['file_pruned'] ?? false)
                <x-alert type="warning" class="mt-3">{{ __('reports.file_pruned') }}</x-alert>
            @elseif ($record['status']['value'] === 'failed')
                <x-alert class="mt-3">{{ $record['error'] ?? __('reports.failed') }}</x-alert>
            @endif
        </x-card>

        <div class="space-y-2">
            @if ($record['is_ready'] && $user->can('reports.download'))
                <x-button class="w-full" wire:click="share" wire:loading.attr="disabled" wire:target="share">
                    <span wire:loading.remove wire:target="share">{{ __('reports.share') }}</span>
                    <span wire:loading wire:target="share">{{ __('app.loading') }}</span>
                </x-button>
            @endif
            @if ($user->can('reports.create'))
                <div class="grid grid-cols-2 gap-2">
                    <x-button variant="secondary" wire:click="regenerate" wire:loading.attr="disabled">{{ __('reports.regenerate') }}</x-button>
                    <x-button variant="secondary" wire:click="duplicate" wire:loading.attr="disabled">{{ __('reports.duplicate') }}</x-button>
                </div>
            @endif
            @if ($user->can('reports.delete'))
                <x-confirm-button action="delete" :label="__('reports.delete')" :confirm="__('reports.confirm_delete')" />
            @endif
        </div>

        <x-card>
            <h3 class="mb-2 font-bold text-primary-700">{{ __('reports.students') }}</h3>
            <ol class="divide-y divide-slate-100 text-sm">
                @foreach ($students as $item)
                    <li wire:key="rs-{{ $item['order'] }}" class="flex justify-between py-2">
                        <span><span class="text-slate-400">{{ $item['order'] }}.</span> {{ $item['student']['name'] ?? '' }}</span>
                        <span class="text-slate-500">{{ $item['student']['grade']['name'] ?? '' }}</span>
                    </li>
                @endforeach
            </ol>
            @if ($hasMoreStudents)
                <x-button variant="ghost" class="mt-2 w-full" wire:click="loadMoreStudents">{{ __('reports.load_more') }}</x-button>
            @endif
        </x-card>
    @endif
</div>
