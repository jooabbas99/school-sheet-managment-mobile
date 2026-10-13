<?php

namespace App\Services\Api;

class UsersApi
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @param  array<string, mixed>  $filters  search, role, institute_id
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function list(array $filters = [], int $page = 1): array
    {
        return $this->api->get('users', array_filter($filters, fn ($v) => $v !== null && $v !== '') + ['page' => $page]);
    }

    /**
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        return $this->api->get("users/{$id}")['data'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function save(?int $id, array $data): array
    {
        return $id === null ? $this->api->post('users', $data)['data'] : $this->api->put("users/{$id}", $data)['data'];
    }

    public function delete(int $id): void
    {
        $this->api->delete("users/{$id}");
    }
}
