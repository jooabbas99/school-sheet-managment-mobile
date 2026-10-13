<?php

use Illuminate\Support\Facades\Http;

it('builds the bottom bar from permissions', function (array $permissions, array $visible, array $hidden) {
    signIn($permissions);

    $response = $this->get('/home')->assertOk()->assertSee('dir="rtl"', false);

    // Check the section links (labels like "الطلاب" also appear inside the app name).
    foreach ($visible as $route) {
        $response->assertSee('href="'.route($route).'"', false);
    }
    foreach ($hidden as $route) {
        $response->assertDontSee('href="'.route($route).'"', false);
    }
})->with([
    'viewer' => [['students.view', 'reports.view', 'reports.download'], ['home', 'students.index', 'reports.index', 'more'], ['templates.index']],
    'admin' => [['students.view', 'reports.view', 'templates.view', 'templates.manage', 'activity.view'], ['students.index', 'reports.index', 'templates.index'], []],
    'no sections' => [[], ['home', 'more'], ['students.index', 'reports.index', 'templates.index']],
]);

it('lists admin sections on the more screen only with permission', function () {
    signIn(['students.view', 'users.manage', 'lookups.manage', 'activity.view']);
    $this->get('/more')->assertOk()
        ->assertSee('المستخدمون')->assertSee('البيانات الأساسية')->assertSee('سجل النشاط')->assertSee('لوحة المتابعة')
        ->assertSee('تسجيل الخروج');

    signIn(['students.view']);
    $this->get('/more')->assertOk()->assertDontSee('المستخدمون')->assertDontSee('سجل النشاط');
});

it('blocks sections the user may not open', function () {
    signIn(['students.view']);
    Http::fake([
        API.'/students*' => Http::response(['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]]),
        API.'/lookups' => Http::response(['data' => ['institutes' => [], 'grades' => [], 'case_types' => []]]),
    ]);

    $this->get('/students')->assertOk();
    $this->get('/users')->assertForbidden();
    $this->get('/templates')->assertForbidden();
});

it('greets the user with role and institute', function () {
    signIn();

    $this->get('/home')->assertSee('أهلًا، محمد الموظف')->assertSee('الصلاحية: موظف')->assertSee('المعهد: معهد النور');
});
