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

/**
 * A student as returned by the backend's StudentResource.
 *
 * @return array<string, mixed>
 */
function apiStudent(int $id = 1, array $overrides = []): array
{
    return array_replace_recursive([
        'id' => $id,
        'name' => "طالب {$id}",
        'national_id' => '31201150101234',
        'address' => 'المنصورة',
        'birth_date' => '2012-01-15',
        'gender' => ['value' => 'male', 'label' => 'ذكر'],
        'status' => ['value' => 'active', 'label' => 'نشط'],
        'notes' => null,
        'grade' => ['id' => 3, 'institute_id' => 1, 'name' => 'الصف الأول', 'order' => 1],
        'institute' => ['id' => 1, 'name' => 'معهد النور', 'has_logo' => false],
        'guardian' => ['id' => 9, 'name' => 'علي حسن', 'phone' => '01012345678', 'national_id' => null, 'address' => null],
    ], $overrides);
}

/**
 * @param  list<array<string, mixed>>  $data
 * @return array<string, mixed>
 */
function apiPage(array $data, int $page = 1, int $lastPage = 1, ?int $total = null): array
{
    return ['data' => $data, 'meta' => ['current_page' => $page, 'last_page' => $lastPage, 'total' => $total ?? count($data)]];
}

/**
 * @return array<string, mixed>
 */
function apiReport(int $id = 5, string $status = 'ready'): array
{
    return [
        'id' => $id,
        'title' => 'كشف الطلاب - لم يدفع',
        'status' => ['value' => $status, 'label' => ['ready' => 'جاهز', 'pending' => 'جاري الإنشاء', 'failed' => 'فشل'][$status]],
        'error' => $status === 'failed' ? 'فشل إنشاء ملف التقرير.' : null,
        'is_ready' => $status === 'ready',
        'students_count' => 2,
        'file_name' => 'كشف الطلاب - 2026-10-14.xlsx',
        'template' => ['id' => 1, 'name' => 'الكشف الافتراضي'],
        'case_type' => ['id' => 2, 'name' => 'لم يدفع', 'color' => '#DC2626', 'is_active' => true],
        'institute' => ['id' => 1, 'name' => 'معهد النور', 'has_logo' => false],
        'creator' => ['id' => 7, 'name' => 'محمد الموظف'],
        'created_at' => '2026-10-14T10:00:00+03:00',
    ];
}

function fakeLookups(): void
{
    Http::fake([API.'/lookups' => Http::response(['data' => [
        'institutes' => [['id' => 1, 'name' => 'معهد النور', 'has_logo' => false]],
        'grades' => [['id' => 3, 'institute_id' => 1, 'name' => 'الصف الأول', 'order' => 1]],
        'case_types' => [
            ['id' => 2, 'name' => 'لم يدفع', 'color' => '#DC2626', 'is_active' => true],
            ['id' => 4, 'name' => 'قديم', 'color' => '#000000', 'is_active' => false],
        ],
    ]])]);
}
