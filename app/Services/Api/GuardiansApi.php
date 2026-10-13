<?php

namespace App\Services\Api;

class GuardiansApi
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $search): array
    {
        return $this->api->get('guardians', ['search' => $search])['data'];
    }

    /**
     * Guardian with their students.
     *
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        return $this->api->get("guardians/{$id}")['data'];
    }
}
