<?php

namespace App\Services\Api;

use App\Services\Auth\TokenStore;
use App\Support\CurrentUser;
use Throwable;

class AuthApi
{
    public function __construct(
        private readonly ApiClient $api,
        private readonly TokenStore $tokens,
        private readonly CurrentUser $currentUser,
    ) {}

    /**
     * POST /login; keeps the token on the device and the user in the session.
     */
    public function login(string $email, string $password): void
    {
        $response = $this->api->post('login', [
            'email' => $email,
            'password' => $password,
            'device_name' => config('api.device_name'),
        ]);

        $this->tokens->put($response['token']);
        $this->currentUser->set($response['user']);
    }

    /**
     * GET /me; refreshes the user (role and permissions may have changed).
     */
    public function refresh(): void
    {
        $this->currentUser->set($this->api->get('me')['data']);
    }

    /**
     * Revoke the token on the server when possible; always sign out locally.
     */
    public function logout(): void
    {
        try {
            if ($this->tokens->get()) {
                $this->api->post('logout');
            }
        } catch (Throwable) {
            // Offline or already revoked: still sign out on the device.
        } finally {
            $this->tokens->clear();
            $this->currentUser->forget();
        }
    }
}
