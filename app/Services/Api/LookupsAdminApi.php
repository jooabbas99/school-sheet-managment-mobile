<?php

namespace App\Services\Api;

/**
 * Changes to institutes, grades and case types (lookups.manage) and institute logos.
 * Clears the cached lookups after every change.
 */
class LookupsAdminApi
{
    /** type => API resource */
    public const RESOURCES = [
        'institutes' => 'institutes',
        'grades' => 'grades',
        'case_types' => 'case_types',
    ];

    public function __construct(
        private readonly ApiClient $api,
        private readonly LookupsApi $lookups,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function save(string $type, ?int $id, array $data): array
    {
        $resource = self::RESOURCES[$type];

        $saved = $id === null
            ? $this->api->post($resource, $data)['data']
            : $this->api->put("{$resource}/{$id}", $data)['data'];

        $this->lookups->forget();

        return $saved;
    }

    public function delete(string $type, int $id): void
    {
        $this->api->delete(self::RESOURCES[$type].'/'.$id);
        $this->lookups->forget();
    }

    public function logo(int $instituteId): string
    {
        return $this->api->download("institutes/{$instituteId}/logo");
    }

    public function uploadLogo(int $instituteId, string $path, string $fileName): void
    {
        $this->api->upload("institutes/{$instituteId}/logo", 'logo', $path, $fileName);
        $this->lookups->forget();
    }

    public function deleteLogo(int $instituteId): void
    {
        $this->api->delete("institutes/{$instituteId}/logo");
        $this->lookups->forget();
    }
}
