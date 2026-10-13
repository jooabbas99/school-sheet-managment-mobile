<?php

namespace App\Services\Api;

class TemplatesApi
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        return $this->api->get('templates')['data'];
    }

    /**
     * Column sources for the builder: key, label, format, width, force_text.
     *
     * @return list<array<string, mixed>>
     */
    public function sources(): array
    {
        return $this->api->get('templates/sources')['data'];
    }

    /**
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        return $this->api->get("templates/{$id}")['data'];
    }

    /**
     * @param  array<string, mixed>  $data  name, title, settings, columns
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->api->post('templates', $data)['data'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(int $id, array $data): array
    {
        return $this->api->put("templates/{$id}", $data)['data'];
    }

    public function delete(int $id): void
    {
        $this->api->delete("templates/{$id}");
    }

    /**
     * @return array<string, mixed>
     */
    public function duplicate(int $id): array
    {
        return $this->api->post("templates/{$id}/duplicate")['data'];
    }

    /**
     * @return array<string, mixed>
     */
    public function makeDefault(int $id): array
    {
        return $this->api->post("templates/{$id}/default")['data'];
    }

    /**
     * Preview an unsaved template with sample students: {settings, columns, rows}.
     *
     * @param  array<string, mixed>  $data
     * @return array{settings: array<string, mixed>, columns: list<array<string, mixed>>, rows: list<list<string>>}
     */
    public function preview(array $data): array
    {
        return $this->api->post('templates/preview', $data)['data'];
    }

    /**
     * Sample xlsx of an unsaved template: signed link {url, expires_at, file_name}.
     *
     * @param  array<string, mixed>  $data
     * @return array{url: string, expires_at: string, file_name: string}
     */
    public function sample(array $data): array
    {
        return $this->api->post('templates/sample', $data)['data'];
    }
}
