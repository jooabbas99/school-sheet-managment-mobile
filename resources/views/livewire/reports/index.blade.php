<div>
    <x-flash />
    <x-api-error :message="$errorMessage" :offline="$offline" />

    <div class="grid grid-cols-2 gap-2">
        <x-select wire:model.live="status" :options="['ready' => __('enums.report_status.ready'), 'pending' => __('enums.report_status.pending'), 'failed' => __('enums.report_status.failed')]"
                  :placeholder="__('reports.all_statuses')" aria-label="{{ __('students.fields.status') }}" />
        <x-select wire:model.live="caseTypeId" :options="$caseTypes" :placeholder="__('reports.all_case_types')" aria-label="{{ __('reports.case_type') }}" />
        <x-field :label="__('reports.from')" for="from"><x-input id="from" type="date" wire:model.live="from" /></x-field>
        <x-field :label="__('reports.to')" for="to"><x-input id="to" type="date" wire:model.live="to" /></x-field>
    </div>

    <ul class="mt-4 space-y-2" wire:loading.class="opacity-60">
        @forelse ($reports as $report)
            <li wire:key="report-{{ $report['id'] }}">
                <a href="{{ route('reports.show', $report['id']) }}" wire:navigate class="block rounded-xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-bold">{{ $report['title'] }}</p>
                        <x-status-badge :status="$report['status']" />
                    </div>
                    <p class="mt-1 flex justify-between text-sm text-slate-500">
                        <span>{{ __('reports.students_count', ['count' => $report['students_count']]) }}</span>
                        <span dir="ltr">{{ \Illuminate\Support\Carbon::parse($report['created_at'])->format('Y-m-d H:i') }}</span>
                    </p>
                    @isset($report['creator']['name'])
                        <p class="text-xs text-slate-400">{{ __('reports.created_by', ['name' => $report['creator']['name']]) }}</p>
                    @endisset
                </a>
            </li>
        @empty
            @unless ($errorMessage)
                <li class="py-10 text-center text-slate-500">{{ __('reports.empty') }}</li>
            @endunless
        @endforelse
    </ul>

    @if ($hasMore)
        <x-button variant="secondary" class="mt-4 w-full" wire:click="loadMore">{{ __('reports.load_more') }}</x-button>
    @endif

    @if (app(\App\Support\CurrentUser::class)->can('reports.create'))
        <x-fab :href="route('reports.create')" :label="__('reports.new')" />
    @endif
</div>
