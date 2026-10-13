<?php

namespace App\Services\Auth;

use Throwable;

/**
 * Prefers SecureStorage; falls back to the encrypted file when secure storage is missing or fails.
 */
class AutoTokenStore implements TokenStore
{
    public function __construct(
        private readonly SecureTokenStore $secure,
        private readonly FileTokenStore $file,
    ) {}

    public function get(): ?string
    {
        if (SecureTokenStore::available()) {
            try {
                $token = $this->secure->get();

                if ($token !== null) {
                    return $token;
                }
            } catch (Throwable) {
                // fall through to the file store
            }
        }

        return $this->file->get();
    }

    public function put(string $token): void
    {
        if (SecureTokenStore::available()) {
            try {
                $this->secure->put($token);
                $this->file->clear();

                return;
            } catch (Throwable) {
                // fall through to the file store
            }
        }

        $this->file->put($token);
    }

    public function clear(): void
    {
        if (SecureTokenStore::available()) {
            try {
                $this->secure->clear();
            } catch (Throwable) {
                // nothing stored there
            }
        }

        $this->file->clear();
    }
}
