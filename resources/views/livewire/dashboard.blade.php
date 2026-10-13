<div class="space-y-4">
    <x-api-error :message="$errorMessage" :offline="$offline" />

    @if ($data)
        <div class="grid grid-cols-2 gap-3">
            <x-card>
                <p class="text-sm text-slate-500">{{ __('dashboard.students') }}</p>
                <p class="text-2xl font-bold text-primary-700">{{ $data['students']['total'] }}</p>
                <p class="text-xs text-slate-500">{{ __('dashboard.active', ['count' => $data['students']['active']]) }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-slate-500">{{ __('dashboard.reports') }}</p>
                <p class="text-2xl font-bold text-primary-700">{{ $data['reports']['this_month'] }}</p>
                <p class="text-xs text-slate-500">{{ __('dashboard.this_month') }} · {{ $data['reports']['total'] }}</p>
            </x-card>
            <x-card class="col-span-2">
                <p class="text-sm text-slate-500">{{ __('dashboard.guardians') }}</p>
                <p class="text-2xl font-bold text-primary-700">{{ $data['guardians'] }}</p>
            </x-card>
        </div>

        <x-card>
            <h2 class="mb-3 font-bold">{{ __('dashboard.per_case_type') }}</h2>
            @php($max = max(1, ...array_column($data['students_per_case_type'], 'students')))
            <ul class="space-y-2">
                @forelse ($data['students_per_case_type'] as $row)
                    <li>
                        <div class="mb-1 flex justify-between text-sm"><span>{{ $row['case_type']['name'] }}</span><span class="font-bold">{{ $row['students'] }}</span></div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full" style="width: {{ round($row['students'] / $max * 100) }}%; background: {{ $row['case_type']['color'] }}"></div>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">{{ __('dashboard.none') }}</li>
                @endforelse
            </ul>
        </x-card>

        <x-card>
            <h2 class="mb-3 font-bold">{{ __('dashboard.per_month') }}</h2>
            @php($maxMonth = max(1, ...array_column($data['reports_per_month'], 'count')))
            <div class="flex h-36 items-end gap-1" dir="ltr">
                @foreach ($data['reports_per_month'] as $month)
                    <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1" title="{{ $month['label'] }}: {{ $month['count'] }}">
                        <span class="text-[10px] text-slate-500">{{ $month['count'] ?: '' }}</span>
                        <div class="w-full rounded-t bg-primary-600" style="height: {{ max(2, round($month['count'] / $maxMonth * 100)) }}%"></div>
                        <span class="text-[10px] text-slate-500">{{ substr($month['month'], 5) }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-2 font-bold">{{ __('dashboard.latest') }}</h2>
            <ul class="divide-y divide-slate-100">
                @forelse ($data['latest_reports'] as $report)
                    <li><a href="{{ route('reports.show', $report['id']) }}" wire:navigate class="flex items-center justify-between gap-2 py-2 text-sm">
                        <span class="min-w-0 truncate">{{ $report['title'] }}</span><x-status-badge :status="$report['status']" /></a></li>
                @empty
                    <li class="text-sm text-slate-500">{{ __('dashboard.none') }}</li>
                @endforelse
            </ul>
        </x-card>
    @endif
</div>
