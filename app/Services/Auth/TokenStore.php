<?php

namespace App\Services\Auth;

/**
 * Keeps the API bearer token on the device.
 */
interface TokenStore
{
    public function get(): ?string;

    public function put(string $token): void;

    public function clear(): void;
}
