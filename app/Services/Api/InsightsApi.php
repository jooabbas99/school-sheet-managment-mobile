<?php

namespace App\Services\Api;

/**
 * Dashboard numbers and the activity log.
 */
class InsightsApi
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        return $this->api->get('dashboard')['data'];
    }

    /**
     * @param  array<string, mixed>  $filters  subject_type, event, causer_id, from, to
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function activity(array $filters = [], int $page = 1): array
    {
        return $this->api->get('activity', array_filter($filters, fn ($v) => $v !== null && $v !== '') + ['page' => $page]);
    }
}
