<?php

namespace App\Services\Api;

class ReportsApi
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @param  array<string, mixed>  $filters  search, from, to, case_type_id, status
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function list(array $filters = [], int $page = 1): array
    {
        return $this->api->get('reports', array_filter($filters, fn ($v) => $v !== null && $v !== '') + ['page' => $page]);
    }

    /**
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        return $this->api->get("reports/{$id}")['data'];
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function students(int $id, int $page = 1): array
    {
        return $this->api->get("reports/{$id}/students", ['page' => $page]);
    }

    /**
     * @param  array{template_id?: int|null, case_type_id?: int|null, student_ids: list<int>, title?: string|null}  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->api->post('reports', array_filter($data, fn ($v) => $v !== null && $v !== ''))['data'];
    }

    /**
     * @return array<string, mixed>
     */
    public function regenerate(int $id): array
    {
        return $this->api->post("reports/{$id}/regenerate")['data'];
    }

    /**
     * @return array<string, mixed>
     */
    public function duplicate(int $id): array
    {
        return $this->api->post("reports/{$id}/duplicate")['data'];
    }

    public function delete(int $id): void
    {
        $this->api->delete("reports/{$id}");
    }

    /**
     * Signed download link (valid 15 minutes, no token needed).
     *
     * @return array{url: string, expires_at: string, file_name: string}
     */
    public function link(int $id): array
    {
        return $this->api->get("reports/{$id}/download")['data'];
    }

    public function fetch(string $signedUrl): string
    {
        return $this->api->downloadSigned($signedUrl);
    }
}
