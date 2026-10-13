<?php

namespace App\Support;

/**
 * The signed-in user as returned by GET /me, kept in the session.
 * Permissions only drive what the app shows; the server enforces them on every request.
 */
class CurrentUser
{
    private const KEY = 'api_user';

    /**
     * @param  array<string, mixed>  $user
     */
    public function set(array $user): void
    {
        session()->put(self::KEY, $user);
    }

    public function forget(): void
    {
        session()->forget(self::KEY);
    }

    public function check(): bool
    {
        return session()->has(self::KEY);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function data(): ?array
    {
        $user = session()->get(self::KEY);

        return is_array($user) ? $user : null;
    }

    public function name(): ?string
    {
        return $this->data()['name'] ?? null;
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->data()['permissions'] ?? [], true);
    }
}
