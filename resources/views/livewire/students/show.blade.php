@php($user = app(\App\Support\CurrentUser::class))
<div class="space-y-4">
    <x-flash />
    <x-api-error :message="$errorMessage" :offline="$offline" />

    @if ($record)
        <x-card>
            <h2 class="mb-3 font-bold text-primary-700">{{ __('students.section_student') }}</h2>
            <dl class="grid grid-cols-3 gap-y-2 text-sm">
                <dt class="text-slate-500">{{ __('students.fields.national_id') }}</dt><dd class="col-span-2 font-medium" dir="ltr" style="text-align: right">{{ $record['national_id'] }}</dd>
                <dt class="text-slate-500">{{ __('students.fields.grade') }}</dt><dd class="col-span-2">{{ $record['grade']['name'] ?? __('app.none') }}</dd>
                <dt class="text-slate-500">{{ __('students.fields.institute') }}</dt><dd class="col-span-2">{{ $record['institute']['name'] ?? __('app.none') }}</dd>
                <dt class="text-slate-500">{{ __('students.fields.birth_date') }}</dt><dd class="col-span-2">{{ $record['birth_date'] ?? __('app.none') }}</dd>
                <dt class="text-slate-500">{{ __('students.fields.gender') }}</dt><dd class="col-span-2">{{ $record['gender']['label'] ?? __('app.none') }}</dd>
                <dt class="text-slate-500">{{ __('students.fields.status') }}</dt><dd class="col-span-2"><x-status-badge :status="$record['status']" /></dd>
                <dt class="text-slate-500">{{ __('students.fields.address') }}</dt><dd class="col-span-2">{{ $record['address'] }}</dd>
                @if ($record['notes'])
                    <dt class="text-slate-500">{{ __('students.fields.notes') }}</dt><dd class="col-span-2">{{ $record['notes'] }}</dd>
                @endif
            </dl>
        </x-card>

        <x-card>
            <h2 class="mb-3 font-bold text-primary-700">{{ __('students.section_guardian') }}</h2>
            <dl class="grid grid-cols-3 gap-y-2 text-sm">
                <dt class="text-slate-500">{{ __('students.fields.guardian_name') }}</dt><dd class="col-span-2 font-medium">{{ $record['guardian']['name'] }}</dd>
                <dt class="text-slate-500">{{ __('students.fields.guardian_phone') }}</dt>
                <dd class="col-span-2"><a href="tel:{{ $record['guardian']['phone'] }}" class="font-medium text-primary-700" dir="ltr">{{ $record['guardian']['phone'] }}</a></dd>
                <dt class="text-slate-500">{{ __('students.fields.guardian_national_id') }}</dt><dd class="col-span-2" dir="ltr" style="text-align: right">{{ $record['guardian']['national_id'] ?? __('app.none') }}</dd>
                <dt class="text-slate-500">{{ __('students.fields.guardian_address') }}</dt><dd class="col-span-2">{{ $record['guardian']['address'] ?? __('app.none') }}</dd>
            </dl>

            @if ($siblings)
                <h3 class="mt-4 mb-2 text-sm font-bold">{{ __('students.siblings') }}</h3>
                <ul class="space-y-1 text-sm">
                    @foreach ($siblings as $sibling)
                        <li><a href="{{ route('students.show', $sibling['id']) }}" wire:navigate class="text-primary-700">{{ $sibling['name'] }}</a>
                            <span class="text-slate-500">- {{ $sibling['grade']['name'] ?? '' }}</span></li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <div class="space-y-2">
            @if ($user->can('students.update'))
                <x-button :href="route('students.edit', $record['id'])" class="w-full">{{ __('students.edit') }}</x-button>
            @endif
            @if ($user->can('students.delete'))
                <x-confirm-button action="delete" :label="__('students.delete')" :confirm="__('students.confirm_delete')" />
            @endif
        </div>
    @endif
</div>
