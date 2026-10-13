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
}
