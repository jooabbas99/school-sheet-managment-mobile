<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/**
 * Encrypted file in the app's private storage (not readable by other apps on Android).
 * Fallback while SecureStorage is unavailable (browser, tests, or no native support).
 */
class FileTokenStore implements TokenStore
{
    private const PATH = 'auth/token';

    public function get(): ?string
    {
        $disk = Storage::disk('local');

        if (! $disk->exists(self::PATH)) {
            return null;
        }

        try {
            return Crypt::decryptString((string) $disk->get(self::PATH)) ?: null;
        } catch (DecryptException) {
            $this->clear();

            return null;
        }
    }

    public function put(string $token): void
    {
        Storage::disk('local')->put(self::PATH, Crypt::encryptString($token));
    }

    public function clear(): void
    {
        Storage::disk('local')->delete(self::PATH);
    }
}
