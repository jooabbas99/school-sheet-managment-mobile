<?php

namespace App\Livewire\Templates;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\ApiClient;
use App\Services\Api\TemplatesApi;
use App\Services\Files\SharedFiles;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Drag-and-drop template builder: columns (add from sources, reorder with wire:sort, style each one),
 * sheet settings, live preview with sample students, sample .xlsx, save.
 */
class Builder extends Component
{
    use HandlesApiErrors;

    public ?int $templateId = null;

    public string $name = '';

    /** columns | sheet | preview */
    public string $tab = 'columns';

    /** @var array<string, mixed> */
    public array $settings = [];

    /** @var list<array<string, mixed>> */
    public array $columns = [];

    /** Index of the column whose settings are open. */
    public ?int $selected = null;

    /** @var list<array<string, mixed>> key, label, format, width, force_text */
    public array $sources = [];

    /** @var array{settings: array<string, mixed>, columns: list<array<string, mixed>>, rows: list<list<string>>}|null */
    public ?array $preview = null;

    public bool $showSources = false;

    public function mount(?int $template = null): void
    {
        $api = app(TemplatesApi::class);
        $this->sources = $this->callApi(fn () => $api->sources()) ?? [];
        $this->settings = self::defaultSettings();

        if ($template === null) {
            return;
        }

        $data = $this->callApi(fn () => $api->find($template), resetErrors: false);

        if ($data !== null) {
            $this->templateId = $template;
            $this->name = $data['name'];
            $this->settings = array_replace($this->settings, $data['settings'] ?? []);
            $this->columns = array_map(fn (array $c) => array_intersect_key($c, array_flip(self::COLUMN_KEYS)), $data['columns']);
        }
    }

    private const COLUMN_KEYS = ['label', 'source', 'width', 'align', 'font_size', 'bold', 'bg_color', 'text_color', 'format', 'is_visible'];

    /**
     * @return array<string, mixed> same defaults as the backend
     */
    public static function defaultSettings(): array
    {
        return [
            'rtl' => true,
            'title_row' => __('templates.default_title_row'),
            'show_logo' => false,
            'header_bg' => '#1F4E78',
            'header_text_color' => '#FFFFFF',
            'borders' => true,
            'freeze_header' => true,
            'paper' => 'A4',
            'orientation' => 'landscape',
            'font_family' => 'Arial',
        ];
    }

    public function addColumn(string $key): void
    {
        $source = collect($this->sources)->firstWhere('key', $key);

        if ($source === null || count($this->columns) >= config('templates.max_columns')) {
            return;
        }

        $this->columns[] = [
            'label' => $source['label'],
            'source' => $key,
            'width' => $source['width'],
            'align' => $key === 'row_number' ? 'center' : 'right',
            'font_size' => 11,
            'bold' => false,
            'bg_color' => null,
            'text_color' => null,
            'format' => $source['format'],
            'is_visible' => true,
        ];

        $this->showSources = false;
        $this->preview = null;
    }

    /**
     * Called by wire:sort when a column is dropped at a new position.
     */
    public function sortColumn(int|string $index, int $position): void
    {
        $index = (int) $index;

        if (! isset($this->columns[$index])) {
            return;
        }

        $column = $this->columns[$index];
        array_splice($this->columns, $index, 1);
        array_splice($this->columns, max(0, min($position, count($this->columns))), 0, [$column]);
        $this->selected = null;
        $this->preview = null;
    }

    public function removeColumn(int $index): void
    {
        unset($this->columns[$index]);
        $this->columns = array_values($this->columns);
        $this->selected = null;
        $this->preview = null;
    }

    public function toggleVisible(int $index): void
    {
        if (isset($this->columns[$index])) {
            $this->columns[$index]['is_visible'] = ! $this->columns[$index]['is_visible'];
            $this->preview = null;
        }
    }

    public function selectColumn(?int $index): void
    {
        $this->selected = $index;
    }

    public function clearColor(int $index, string $field): void
    {
        if (isset($this->columns[$index]) && in_array($field, ['bg_color', 'text_color'], true)) {
            $this->columns[$index][$field] = null;
        }
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'columns') || str_starts_with($property, 'settings')) {
            $this->preview = null;
        }
    }

    public function updatedTab(string $tab): void
    {
        if ($tab === 'preview' && $this->preview === null && $this->columns !== []) {
            $this->refreshPreview();
        }
    }

    public function refreshPreview(): void
    {
        $this->resetErrorBag();
        $this->preview = $this->callApi(fn () => app(TemplatesApi::class)->preview($this->payload()));
    }

    /**
     * Excel sample of the template as it is now (saved or not).
     */
    public function sample(TemplatesApi $api, SharedFiles $files): mixed
    {
        $this->resetErrorBag();
        $link = $this->callApi(fn () => $api->sample($this->payload()));

        if ($link === null) {
            return null;
        }

        if ($files->shareLink($link['url'], $link['file_name'])) {
            return null;
        }

        $content = $this->callApi(fn () => app(ApiClient::class)->downloadSigned($link['url']));

        if ($content === null) {
            return null;
        }

        $name = $files->store($content, $link['file_name']);

        return $files->share($name, $link['file_name']) ? null : $this->redirectRoute('files.show', ['name' => $name]);
    }

    public function save(TemplatesApi $api): void
    {
        $this->resetErrorBag();

        $saved = $this->callApi(fn () => $this->templateId
            ? $api->update($this->templateId, $this->payload())
            : $api->create($this->payload()));

        if ($saved === null) {
            // Field errors for columns/settings show on their tab; open the first tab that has one.
            $this->tab = $this->getErrorBag()->keys() !== [] && collect($this->getErrorBag()->keys())->every(fn ($k) => str_starts_with($k, 'settings'))
                ? 'sheet' : 'columns';

            return;
        }

        session()->flash('status', __('templates.saved'));
        $this->redirectRoute('templates.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'name' => trim($this->name) ?: null,
            'settings' => [
                ...$this->settings,
                'rtl' => (bool) $this->settings['rtl'],
                'show_logo' => (bool) $this->settings['show_logo'],
                'borders' => (bool) $this->settings['borders'],
                'freeze_header' => (bool) $this->settings['freeze_header'],
                'title_row' => trim((string) $this->settings['title_row']) ?: null,
            ],
            'columns' => array_map(fn (array $column) => [
                ...$column,
                'label' => trim((string) $column['label']),
                'width' => (float) $column['width'],
                'font_size' => (int) $column['font_size'],
                'bold' => (bool) $column['bold'],
                'is_visible' => (bool) $column['is_visible'],
                'bg_color' => $column['bg_color'] ?: null,
                'text_color' => $column['text_color'] ?: null,
            ], $this->columns),
        ];
    }

    public function render(): View
    {
        return view('livewire.templates.builder', [
            'sourceLabels' => array_column($this->sources, 'label', 'key'),
            'forcedText' => array_keys(array_filter(array_column($this->sources, 'force_text', 'key'))),
        ])
            ->title(__($this->templateId ? 'templates.edit' : 'templates.new'))
            ->layoutData(['back' => route('templates.index')]);
    }
}
