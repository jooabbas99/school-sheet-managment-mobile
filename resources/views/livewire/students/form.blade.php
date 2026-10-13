<form wire:submit="save" class="space-y-4">
    <x-api-error :message="$errors->any() ? null : $errorMessage" :offline="$offline" />

    <x-card class="space-y-4">
        <h2 class="font-bold text-primary-700">{{ __('students.section_student') }}</h2>

        <x-field :label="__('students.fields.name')" for="name" error="name">
            <x-input id="name" wire:model="name" autocomplete="off" />
        </x-field>

        <x-field :label="__('students.fields.national_id')" for="national_id" error="national_id" :hint="__('students.national_id_hint')">
            <x-input id="national_id" wire:model.live.debounce.500ms="national_id" inputmode="numeric" maxlength="20" ltr />
        </x-field>

        <x-field :label="__('students.fields.grade')" for="grade_id" error="grade_id">
            <x-select id="grade_id" wire:model="grade_id" :options="$grades" :placeholder="__('students.choose')" />
        </x-field>

        <div class="grid grid-cols-2 gap-3">
            <x-field :label="__('students.fields.birth_date')" for="birth_date" error="birth_date">
                <x-input id="birth_date" type="date" wire:model="birth_date" />
            </x-field>
            <x-field :label="__('students.fields.gender')" for="gender" error="gender">
                <x-select id="gender" wire:model="gender" :options="$genders" :placeholder="__('students.choose')" />
            </x-field>
        </div>

        <x-field :label="__('students.fields.address')" for="address" error="address">
            <x-input id="address" wire:model="address" />
        </x-field>

        @if ($studentId)
            <x-field :label="__('students.fields.status')" for="status" error="status">
                <x-select id="status" wire:model="status" :options="['active' => __('enums.student_status.active'), 'inactive' => __('enums.student_status.inactive')]" />
            </x-field>
        @endif

        <x-field :label="__('students.fields.notes')" for="notes" error="notes">
            <textarea id="notes" wire:model="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2.5"></textarea>
        </x-field>
    </x-card>

    <x-card class="space-y-4">
        <h2 class="font-bold text-primary-700">{{ __('students.section_guardian') }}</h2>

        <div class="grid grid-cols-2 rounded-lg bg-slate-100 p-1 text-sm">
            <button type="button" wire:click="$set('guardianMode', 'existing')"
                    @class(['rounded-md py-2', 'bg-white font-bold shadow-sm' => $guardianMode === 'existing'])>{{ __('students.guardian_existing') }}</button>
            <button type="button" wire:click="$set('guardianMode', 'new')"
                    @class(['rounded-md py-2', 'bg-white font-bold shadow-sm' => $guardianMode === 'new'])>{{ __('students.guardian_new') }}</button>
        </div>

        @if ($guardianMode === 'existing')
            @if ($pickedGuardian)
                <div class="flex items-center justify-between rounded-lg border border-primary-600 bg-primary-50 p-3">
                    <div>
                        <p class="font-bold">{{ $pickedGuardian['name'] }}</p>
                        <p class="text-sm text-slate-600" dir="ltr" style="text-align: right">{{ $pickedGuardian['phone'] }}</p>
                    </div>
                    <button type="button" wire:click="clearGuardian" class="text-sm font-bold text-primary-700">{{ __('students.guardian_change') }}</button>
                </div>
            @else
                <x-field :label="__('students.guardian_search')" for="guardianSearch" error="guardian_id">
                    <x-input id="guardianSearch" type="search" wire:model.live.debounce.400ms="guardianSearch" autocomplete="off" />
                </x-field>
                @if ($guardianResults)
                    <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                        @foreach ($guardianResults as $result)
                            <li wire:key="guardian-{{ $result['id'] }}">
                                <button type="button" wire:click="pickGuardian({{ $result['id'] }})" class="flex w-full justify-between px-3 py-3 text-right">
                                    <span>{{ $result['name'] }}
                                        <span class="block text-xs text-slate-500">{{ __('students.guardian_children', ['count' => $result['students_count'] ?? 0]) }}</span></span>
                                    <span class="text-sm text-slate-500" dir="ltr">{{ $result['phone'] }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @elseif (mb_strlen(trim($guardianSearch)) >= 2)
                    <p class="text-sm text-slate-500">{{ __('students.guardian_none') }}</p>
                @endif
            @endif
        @else
            <x-field :label="__('students.fields.guardian_name')" for="guardian_name" error="guardian.name">
                <x-input id="guardian_name" wire:model="guardian.name" />
            </x-field>
            <x-field :label="__('students.fields.guardian_phone')" for="guardian_phone" error="guardian.phone">
                <x-input id="guardian_phone" type="tel" wire:model="guardian.phone" inputmode="tel" ltr />
            </x-field>
            <x-field :label="__('students.fields.guardian_national_id')" for="guardian_national_id" error="guardian.national_id">
                <x-input id="guardian_national_id" wire:model="guardian.national_id" inputmode="numeric" ltr />
            </x-field>
            <x-field :label="__('students.fields.guardian_address')" for="guardian_address" error="guardian.address">
                <x-input id="guardian_address" wire:model="guardian.address" />
            </x-field>
        @endif
    </x-card>

    <x-button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="save">
        <span wire:loading.remove wire:target="save">{{ __('app.save') }}</span>
        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
    </x-button>
</form>
