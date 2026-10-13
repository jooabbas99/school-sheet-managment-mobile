<div class="space-y-4 pb-16">
    <x-api-error :message="$errorMessage" :offline="$offline" />

    <x-field :label="__('templates.name')" for="name" error="name">
        <x-input id="name" wire:model="name" />
    </x-field>

    {{-- Tabs --}}
    <div class="grid grid-cols-3 rounded-lg bg-slate-100 p-1 text-sm">
        @foreach (['columns', 'sheet', 'preview'] as $key)
            <button type="button" wire:click="$set('tab', '{{ $key }}')"
                    @class(['rounded-md py-2', 'bg-white font-bold shadow-sm' => $tab === $key])>{{ __("templates.tabs.$key") }}</button>
        @endforeach
    </div>

    @if ($tab === 'columns')
        @error('columns') <x-alert>{{ $message }}</x-alert> @enderror
        <p class="text-xs text-slate-500">{{ __('templates.add_hint') }}</p>

        {{-- Column list: drag by the handle to reorder (wire:sort) --}}
        <ul wire:sort="sortColumn" class="space-y-2">
            @forelse ($columns as $index => $column)
                <li wire:key="col-{{ $index }}-{{ $column['source'] }}" wire:sort:item="{{ $index }}"
                    @class(['flex items-center gap-2 rounded-xl bg-white p-2 shadow-sm', 'opacity-50' => ! $column['is_visible']])>
                    <span wire:sort:handle class="cursor-grab touch-none px-2 py-3 text-xl leading-none text-slate-400" aria-label="{{ __('templates.drag') }}">&#8942;&#8942;</span>
                    <button type="button" wire:click="selectColumn({{ $index }})" class="min-w-0 flex-1 text-right">
                        <span class="block truncate font-medium">{{ $column['label'] ?: '—' }}</span>
                        <span class="block truncate text-xs text-slate-500">{{ $sourceLabels[$column['source']] ?? $column['source'] }}
                            @unless ($column['is_visible']) · {{ __('templates.hidden') }} @endunless</span>
                    </button>
                    <button type="button" wire:click="toggleVisible({{ $index }})" class="px-2 py-2 text-xs text-primary-700">
                        {{ $column['is_visible'] ? __('templates.hide') : __('templates.show') }}
                    </button>
                    <button type="button" wire:click="removeColumn({{ $index }})" class="px-2 py-2 text-lg leading-none text-red-600" aria-label="{{ __('templates.remove') }}">&times;</button>
                    @if ($errors->has("columns.$index.label") || $errors->has("columns.$index.source"))
                        <span class="sr-only">!</span>
                    @endif
                </li>
                @foreach (['label', 'source', 'width', 'font_size', 'bg_color', 'text_color'] as $field)
                    @error("columns.$index.$field") <li class="text-sm text-red-600">{{ $message }}</li> @enderror
                @endforeach
            @empty
                <li class="rounded-xl bg-white p-6 text-center text-slate-500 shadow-sm">{{ __('templates.no_columns') }}</li>
            @endforelse
        </ul>

        {{-- Sources palette --}}
        <div>
            <x-button variant="secondary" class="w-full" wire:click="$toggle('showSources')">+ {{ __('templates.add_column') }}</x-button>
            @if ($showSources)
                <div class="mt-2 grid grid-cols-2 gap-2">
                    @foreach ($sources as $source)
                        <button type="button" wire:click="addColumn('{{ $source['key'] }}')" wire:key="src-{{ $source['key'] }}"
                                class="rounded-lg border border-primary-600 bg-white px-2 py-2.5 text-sm text-primary-700">{{ $source['label'] }}</button>
                    @endforeach
                </div>
            @endif
        </div>
    @elseif ($tab === 'sheet')
        <x-card class="space-y-4">
            <x-field :label="__('templates.settings.title_row')" for="title_row" error="settings.title_row">
                <x-input id="title_row" wire:model="settings.title_row" />
            </x-field>

            <div class="grid grid-cols-2 gap-3">
                <x-field :label="__('templates.settings.header_bg')" for="header_bg" error="settings.header_bg">
                    <input id="header_bg" type="color" wire:model.live="settings.header_bg" class="h-11 w-full rounded-lg border border-slate-300">
                </x-field>
                <x-field :label="__('templates.settings.header_text_color')" for="header_text_color" error="settings.header_text_color">
                    <input id="header_text_color" type="color" wire:model.live="settings.header_text_color" class="h-11 w-full rounded-lg border border-slate-300">
                </x-field>
                <x-field :label="__('templates.settings.paper')" for="paper" error="settings.paper">
                    <x-select id="paper" wire:model="settings.paper" :options="array_combine(config('templates.papers'), config('templates.papers'))" />
                </x-field>
                <x-field :label="__('templates.settings.orientation')" for="orientation" error="settings.orientation">
                    <x-select id="orientation" wire:model="settings.orientation" :options="['landscape' => __('templates.settings.landscape'), 'portrait' => __('templates.settings.portrait')]" />
                </x-field>
            </div>

            <x-field :label="__('templates.settings.font_family')" for="font_family" error="settings.font_family">
                <x-select id="font_family" wire:model="settings.font_family" :options="array_combine(config('templates.fonts'), config('templates.fonts'))" />
            </x-field>

            <div class="space-y-3">
                @foreach (['rtl', 'show_logo', 'borders', 'freeze_header'] as $flag)
                    <label class="flex items-center justify-between gap-3">
                        <span>{{ __("templates.settings.$flag") }}</span>
                        <input type="checkbox" wire:model="settings.{{ $flag }}" class="h-6 w-6 rounded text-primary-700">
                    </label>
                @endforeach
            </div>
        </x-card>
    @else
        <div class="flex gap-2">
            <x-button variant="secondary" class="flex-1" wire:click="refreshPreview" wire:loading.attr="disabled">{{ __('templates.refresh_preview') }}</x-button>
            <x-button variant="secondary" class="flex-1" wire:click="sample" wire:loading.attr="disabled" wire:target="sample">{{ __('templates.sample') }}</x-button>
        </div>

        @if ($preview)
            @php($s = $preview['settings'])
            <div class="overflow-x-auto rounded-xl bg-white p-2 shadow-sm" dir="{{ $s['rtl'] ? 'rtl' : 'ltr' }}" style="font-family: {{ $s['font_family'] }}">
                @if ($s['title_row'])
                    <p class="mb-2 text-center font-bold">{{ $s['title_row'] }}</p>
                @endif
                <table class="min-w-full border-collapse text-xs">
                    <thead>
                        <tr>
                            @foreach ($preview['columns'] as $column)
                                <th @class(['whitespace-nowrap px-2 py-1.5', 'border border-slate-300' => $s['borders']])
                                    style="background: {{ $s['header_bg'] }}; color: {{ $s['header_text_color'] }}; min-width: {{ (int) ($column['width'] * 7) }}px">{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['rows'] as $row)
                            <tr>
                                @foreach ($row as $i => $cell)
                                    @php($c = $preview['columns'][$i])
                                    <td @class(['whitespace-nowrap px-2 py-1', 'border border-slate-300' => $s['borders'], 'font-bold' => $c['bold']])
                                        style="text-align: {{ $c['align'] }}; font-size: {{ max(9, (int) $c['font_size'] - 1) }}px; @if ($c['bg_color']) background: {{ $c['bg_color'] }}; @endif @if ($c['text_color']) color: {{ $c['text_color'] }}; @endif">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="rounded-xl bg-white p-6 text-center text-slate-500 shadow-sm">{{ __('templates.preview_empty') }}</p>
        @endif
    @endif

    <x-button class="w-full" wire:click="save" wire:loading.attr="disabled" wire:target="save">
        <span wire:loading.remove wire:target="save">{{ __('templates.save') }}</span>
        <span wire:loading wire:target="save">{{ __('app.loading') }}</span>
    </x-button>

    {{-- Column settings: bottom sheet --}}
    @if ($selected !== null && isset($columns[$selected]))
        <div class="fixed inset-0 z-30 bg-black/40" wire:click="selectColumn(null)"></div>
        <div class="fixed inset-x-0 bottom-0 z-40 max-h-[85dvh] space-y-4 overflow-y-auto rounded-t-2xl bg-white p-4 shadow-xl safe-bottom">
            <div class="flex items-center justify-between">
                <h2 class="font-bold">{{ __('templates.column_settings') }}</h2>
                <button type="button" wire:click="selectColumn(null)" class="font-bold text-primary-700">{{ __('templates.done') }}</button>
            </div>
            <p class="text-xs text-slate-500">{{ __('templates.column.source') }}: {{ $sourceLabels[$columns[$selected]['source']] ?? '' }}</p>

            <x-field :label="__('templates.column.label')" for="col_label" :error="'columns.'.$selected.'.label'">
                <x-input id="col_label" wire:model.blur="columns.{{ $selected }}.label" />
            </x-field>

            <div class="grid grid-cols-2 gap-3">
                <x-field :label="__('templates.column.width')" for="col_width">
                    <x-input id="col_width" type="number" min="3" max="100" step="1" wire:model.blur="columns.{{ $selected }}.width" />
                </x-field>
                <x-field :label="__('templates.column.font_size')" for="col_font">
                    <x-input id="col_font" type="number" min="6" max="48" wire:model.blur="columns.{{ $selected }}.font_size" />
                </x-field>
                <x-field :label="__('templates.column.align')" for="col_align">
                    <x-select id="col_align" wire:model="columns.{{ $selected }}.align" :options="__('templates.align')" />
                </x-field>
                <x-field :label="__('templates.column.format')" for="col_format">
                    <x-select id="col_format" wire:model="columns.{{ $selected }}.format" :options="__('templates.format')"
                              :disabled="in_array($columns[$selected]['source'], $forcedText, true)" />
                </x-field>
            </div>
            @if (in_array($columns[$selected]['source'], $forcedText, true))
                <p class="text-xs text-slate-500">{{ __('templates.text_forced') }}</p>
            @endif

            <div class="grid grid-cols-2 gap-3">
                @foreach (['bg_color', 'text_color'] as $colorField)
                    <x-field :label="__('templates.column.'.$colorField)" :for="'col_'.$colorField">
                        <div class="flex items-center gap-2">
                            <input id="col_{{ $colorField }}" type="color" wire:model.live="columns.{{ $selected }}.{{ $colorField }}"
                                   value="{{ $columns[$selected][$colorField] ?? '#ffffff' }}" class="h-11 min-w-0 flex-1 rounded-lg border border-slate-300">
                            @if ($columns[$selected][$colorField])
                                <button type="button" wire:click="clearColor({{ $selected }}, '{{ $colorField }}')" class="text-xs text-slate-500">{{ __('templates.no_color') }}</button>
                            @endif
                        </div>
                    </x-field>
                @endforeach
            </div>

            <label class="flex items-center justify-between">
                <span>{{ __('templates.column.bold') }}</span>
                <input type="checkbox" wire:model="columns.{{ $selected }}.bold" class="h-6 w-6 rounded text-primary-700">
            </label>
            <label class="flex items-center justify-between">
                <span>{{ __('templates.column.visible') }}</span>
                <input type="checkbox" wire:model="columns.{{ $selected }}.is_visible" class="h-6 w-6 rounded text-primary-700">
            </label>
        </div>
    @endif
</div>
