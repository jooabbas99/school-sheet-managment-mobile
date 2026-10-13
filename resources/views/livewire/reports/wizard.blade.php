<div class="space-y-4">
    <x-api-error :message="$errorMessage" :offline="$offline" />

    {{-- Steps --}}
    <ol class="grid grid-cols-4 gap-1 text-center text-xs">
        @foreach (__('reports.wizard.steps') as $index => $label)
            <li @class(['rounded-full py-1', 'bg-primary-700 text-white font-bold' => $step === $index + 1,
                        'bg-primary-50 text-primary-700' => $step > $index + 1, 'bg-slate-100 text-slate-500' => $step < $index + 1])>{{ $label }}</li>
        @endforeach
    </ol>

    @if ($step === 1)
        <x-card class="space-y-2">
            @forelse ($templates as $item)
                <label wire:key="tpl-{{ $item['id'] }}" @class(['flex items-center gap-3 rounded-lg border p-3', 'border-primary-600 bg-primary-50' => $templateId === $item['id'], 'border-slate-200' => $templateId !== $item['id']])>
                    <input type="radio" wire:model.live="templateId" value="{{ $item['id'] }}" class="text-primary-700">
                    <span class="flex-1">{{ $item['name'] }}</span>
                    @if ($item['is_default']) <x-badge color="blue">{{ __('reports.wizard.default') }}</x-badge> @endif
                </label>
            @empty
                <p class="text-slate-500">{{ __('reports.wizard.no_templates') }}</p>
            @endforelse
            @error('templateId') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </x-card>
    @elseif ($step === 2)
        <x-card class="space-y-2">
            <label @class(['flex items-center gap-3 rounded-lg border p-3', 'border-primary-600 bg-primary-50' => $caseTypeId === '', 'border-slate-200' => $caseTypeId !== ''])>
                <input type="radio" wire:model.live="caseTypeId" value=""> {{ __('reports.no_case_type') }}
            </label>
            @foreach ($caseTypes as $id => $name)
                <label wire:key="ct-{{ $id }}" @class(['flex items-center gap-3 rounded-lg border p-3', 'border-primary-600 bg-primary-50' => $caseTypeId === (string) $id, 'border-slate-200' => $caseTypeId !== (string) $id])>
                    <input type="radio" wire:model.live="caseTypeId" value="{{ $id }}"> {{ $name }}
                </label>
            @endforeach
        </x-card>
    @elseif ($step === 3)
        <div class="space-y-2">
            <x-input type="search" wire:model.live.debounce.400ms="search" :placeholder="__('students.search')" aria-label="{{ __('students.search') }}" />
            <div class="flex gap-2">
                <x-select wire:model.live="gradeId" :options="$grades" :placeholder="__('students.all_grades')" aria-label="{{ __('students.fields.grade') }}" />
                <x-select wire:model.live="status" :options="['active' => __('enums.student_status.active'), 'inactive' => __('enums.student_status.inactive')]"
                          :placeholder="__('students.all_statuses')" class="w-36 shrink-0" aria-label="{{ __('students.fields.status') }}" />
            </div>
        </div>

        <div class="sticky top-16 z-10 flex items-center justify-between rounded-lg bg-primary-50 px-3 py-2 text-sm">
            <span class="font-bold text-primary-700">{{ __('reports.wizard.selected', ['count' => count($selected)]) }}</span>
            <div class="flex gap-3">
                <button type="button" wire:click="selectAllFiltered" class="font-bold text-primary-700">{{ __('reports.wizard.select_all', ['count' => $total]) }}</button>
                @if ($selected)
                    <button type="button" wire:click="clearSelection" class="text-slate-500">{{ __('reports.wizard.clear') }}</button>
                @endif
            </div>
        </div>
        @if ($truncated)
            <x-alert type="warning">{{ __('reports.wizard.truncated', ['count' => count($selected)]) }}</x-alert>
        @endif
        @error('selected') <x-alert>{{ $message }}</x-alert> @enderror

        <ul class="space-y-2" wire:loading.class="opacity-60">
            @foreach ($students as $student)
                <li wire:key="pick-{{ $student['id'] }}">
                    <label class="flex items-center gap-3 rounded-xl bg-white p-3 shadow-sm">
                        <input type="checkbox" wire:click="toggle({{ $student['id'] }})" @checked(in_array($student['id'], $selected, true)) class="h-5 w-5 rounded text-primary-700">
                        <span class="flex-1">
                            <span class="block font-medium">{{ $student['name'] }}</span>
                            <span class="block text-xs text-slate-500">{{ $student['grade']['name'] ?? '' }}</span>
                        </span>
                    </label>
                </li>
            @endforeach
        </ul>
        @if ($hasMore)
            <x-button variant="secondary" class="w-full" wire:click="loadMore">{{ __('students.load_more') }}</x-button>
        @endif
    @else
        <x-card class="space-y-3">
            <dl class="grid grid-cols-3 gap-y-2 text-sm">
                <dt class="text-slate-500">{{ __('reports.template') }}</dt><dd class="col-span-2">{{ $template['name'] ?? '' }}</dd>
                <dt class="text-slate-500">{{ __('reports.case_type') }}</dt><dd class="col-span-2">{{ $caseTypes[(int) $caseTypeId] ?? __('reports.no_case_type') }}</dd>
                <dt class="text-slate-500">{{ __('students.title') }}</dt><dd class="col-span-2">{{ count($selected) }}</dd>
            </dl>
            <x-field :label="__('reports.wizard.title_label')" for="title" error="title">
                <x-input id="title" wire:model="title" :placeholder="__('reports.wizard.title_placeholder')" />
            </x-field>
        </x-card>
        <x-card>
            <h3 class="mb-2 text-sm font-bold">{{ __('reports.wizard.preview') }}</h3>
            @php($known = array_values(array_filter(array_map(fn ($id) => $names[$id] ?? null, array_slice($selected, 0, 10)))))
            <ol class="list-decimal space-y-1 ps-5 text-sm">
                @foreach ($known as $name) <li>{{ $name }}</li> @endforeach
            </ol>
            @if (count($selected) > count($known))
                <p class="mt-1 text-sm text-slate-500">{{ __('reports.wizard.and_more', ['count' => count($selected) - count($known)]) }}</p>
            @endif
        </x-card>
    @endif

    <div class="flex gap-2">
        @if ($step > 1)
            <x-button variant="secondary" class="flex-1" wire:click="back">{{ __('reports.wizard.back') }}</x-button>
        @endif
        @if ($step < \App\Livewire\Reports\Wizard::STEPS)
            <x-button class="flex-1" wire:click="next" wire:loading.attr="disabled">{{ __('reports.wizard.next') }}</x-button>
        @else
            <x-button class="flex-1" wire:click="generate" wire:loading.attr="disabled" wire:target="generate">
                <span wire:loading.remove wire:target="generate">{{ __('reports.wizard.generate') }}</span>
                <span wire:loading wire:target="generate">{{ __('app.loading') }}</span>
            </x-button>
        @endif
    </div>
</div>
