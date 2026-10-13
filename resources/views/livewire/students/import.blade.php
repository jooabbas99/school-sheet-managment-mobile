<div class="space-y-4">
    <x-api-error :message="$errors->any() ? null : $errorMessage" :offline="$offline" />

    <x-card class="space-y-4">
        <p class="text-sm text-slate-600">{{ __('students.import_page.intro') }}</p>

        @if ($institutes)
            <x-field :label="__('students.import_page.institute')" for="institute_id" error="institute_id">
                <x-select id="institute_id" wire:model="institute_id" :options="$institutes" :placeholder="__('students.choose')" />
            </x-field>
        @endif

        <x-button variant="secondary" class="w-full" wire:click="downloadTemplate" wire:loading.attr="disabled" wire:target="downloadTemplate">
            {{ __('students.import_page.template') }}
        </x-button>
    </x-card>

    <form wire:submit="import">
        <x-card class="space-y-4">
            <x-field :label="__('students.import_page.file')" for="file" error="file">
                <input id="file" type="file" wire:model="file" accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                       class="w-full text-sm file:me-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:font-bold file:text-primary-700">
            </x-field>
            <x-button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="import,file">
                <span wire:loading.remove wire:target="import">{{ __('students.import_page.submit') }}</span>
                <span wire:loading wire:target="import">{{ __('app.loading') }}</span>
            </x-button>
        </x-card>
    </form>

    @if ($result)
        <x-alert type="success">{{ __('students.import_page.imported', ['count' => $result['imported']]) }}</x-alert>

        @if ($result['failed'] > 0)
            <x-card>
                <p class="mb-2 font-bold text-red-700">{{ __('students.import_page.failed', ['count' => $result['failed']]) }}</p>
                <ul class="space-y-2 text-sm">
                    @foreach ($result['failed_rows'] as $row)
                        <li class="rounded-lg bg-red-50 p-2">
                            <p class="font-bold">{{ __('students.import_page.row', ['row' => $row['row']]) }}</p>
                            <ul class="list-disc ps-5 text-red-700">
                                @foreach ($row['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif
    @endif
</div>
