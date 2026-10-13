<?php

namespace App\Services\Api;

/**
 * Institutes, grades and case types. Cached in the session for a few minutes: they rarely change.
 */
class LookupsApi
{
    private const KEY = 'api_lookups';

    private const TTL_SECONDS = 600;

    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return array{institutes: list<array<string, mixed>>, grades: list<array<string, mixed>>, case_types: list<array<string, mixed>>}
     */
    public function all(): array
    {
        $cached = session(self::KEY);

        if (is_array($cached) && ($cached['at'] ?? 0) > now()->timestamp - self::TTL_SECONDS) {
            return $cached['data'];
        }

        $data = $this->api->get('lookups')['data'];
        session()->put(self::KEY, ['at' => now()->timestamp, 'data' => $data]);

        return $data;
    }

    public function forget(): void
    {
        session()->forget(self::KEY);
    }

    /**
     * Grade options [id => label]. With several institutes (super admin) the institute name is added.
     *
     * @return array<int, string>
     */
    public function gradeOptions(): array
    {
        $lookups = $this->all();
        $institutes = array_column($lookups['institutes'], 'name', 'id');
        $several = count($institutes) > 1;

        $options = [];
        foreach ($lookups['grades'] as $grade) {
            $options[$grade['id']] = $several
                ? $grade['name'].' - '.($institutes[$grade['institute_id']] ?? '')
                : $grade['name'];
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public function caseTypeOptions(bool $activeOnly = true): array
    {
        $options = [];
        foreach ($this->all()['case_types'] as $caseType) {
            if (! $activeOnly || $caseType['is_active']) {
                $options[$caseType['id']] = $caseType['name'];
            }
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public function instituteOptions(): array
    {
        return array_column($this->all()['institutes'], 'name', 'id');
    }
}
