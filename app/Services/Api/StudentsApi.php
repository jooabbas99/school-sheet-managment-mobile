<?php

namespace App\Services\Api;

class StudentsApi
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @param  array<string, mixed>  $filters  search, grade_id, institute_id, status, gender
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->api->get('students', $this->clean($filters) + ['page' => $page, 'per_page' => $perPage]);
    }

    /**
     * IDs of all students matching the filters ("select all filtered").
     *
     * @param  array<string, mixed>  $filters
     * @return array{ids: list<int>, count: int, truncated: bool}
     */
    public function ids(array $filters = []): array
    {
        return $this->api->get('students/ids', $this->clean($filters))['data'];
    }

    /**
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        return $this->api->get("students/{$id}")['data'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->api->post('students', $data)['data'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(int $id, array $data): array
    {
        return $this->api->put("students/{$id}", $data)['data'];
    }

    public function delete(int $id): void
    {
        $this->api->delete("students/{$id}");
    }

    /**
     * @return array{imported: int, failed: int, failed_rows: list<array{row: int, errors: list<string>}>}
     */
    public function import(string $path, string $fileName, ?int $instituteId = null): array
    {
        return $this->api->upload('students/import', 'file', $path, $fileName, ['institute_id' => $instituteId])['data'];
    }

    public function importTemplate(?int $instituteId = null): string
    {
        return $this->api->download('students/import-template', array_filter(['institute_id' => $instituteId]));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function clean(array $filters): array
    {
        return array_filter($filters, fn ($value) => $value !== null && $value !== '');
    }
}
