<?php

use App\Services\Auth\TokenStore;
use App\Services\Connectivity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->beforeEach(function () {
        Storage::fake('local');
        Http::preventStrayRequests();
        setOnline(true);
    })
    ->in('Feature', 'Unit');

const API = 'http://api.test/api/v1';

/**
 * A user as returned by the backend's GET /me.
 *
 * @param  list<string>  $permissions
 * @return array<string, mixed>
 */
function apiUser(array $permissions = [], string $role = 'staff', ?array $institute = ['id' => 1, 'name' => 'معهد النور']): array
{
    return [
        'id' => 7,
        'name' => 'محمد الموظف',
        'email' => 'staff@school.test',
        'is_active' => true,
        'role' => ['value' => $role, 'label' => ['super_admin' => 'مدير النظام', 'admin' => 'مدير', 'staff' => 'موظف', 'viewer' => 'مشاهد'][$role]],
        'institute' => $institute,
        'permissions' => $permissions,
    ];
}

/**
 * Pretend the user is signed in: token on the device and /me data in the session.
 *
 * @param  list<string>  $permissions
 */
function signIn(array $permissions = []): void
{
    app(TokenStore::class)->put('test-token');
    session()->put('api_user', apiUser($permissions));
}

function setOnline(bool $online): void
{
    app()->instance(Connectivity::class, new class($online) extends Connectivity
    {
        public function __construct(private readonly bool $online) {}

        public function isOffline(): bool
        {
            return ! $this->online;
        }
    });
}
