<?php

namespace App\Services\Auth;

use Native\Mobile\SecureStorage;

/**
 * Android Keystore / iOS Keychain through NativePHP SecureStorage. Only works inside the native app.
 */
class SecureTokenStore implements TokenStore
{
    private const KEY = 'api_token';

    public function __construct(private readonly SecureStorage $storage) {}

    public static function available(): bool
    {
        return function_exists('nativephp_call');
    }

    public function get(): ?string
    {
        return $this->storage->get(self::KEY) ?: null;
    }

    public function put(string $token): void
    {
        if (! $this->storage->set(self::KEY, $token)) {
            throw new \RuntimeException('Secure storage rejected the token.');
        }
    }

    public function clear(): void
    {
        $this->storage->delete(self::KEY);
    }
}
