<?php

use App\Livewire\Activity\Index as ActivityIndex;
use App\Livewire\Dashboard;
use App\Livewire\Lookups\Index as LookupsIndex;
use App\Livewire\Lookups\Logo;
use App\Livewire\Templates\Builder;
use App\Livewire\Templates\Index as TemplatesIndex;
use App\Livewire\Users\Form as UserForm;
use App\Livewire\Users\Index as UsersIndex;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function fakeSources(): void
{
    Http::fake([API.'/templates/sources' => Http::response(['data' => [
        ['key' => 'row_number', 'label' => 'م', 'format' => 'number', 'width' => 6, 'force_text' => false],
        ['key' => 'student.name', 'label' => 'اسم الطالب', 'format' => 'text', 'width' => 30, 'force_text' => false],
        ['key' => 'student.national_id', 'label' => 'الرقم القومي للطالب', 'format' => 'text', 'width' => 20, 'force_text' => true],
    ]])]);
}

beforeEach(function () {
    signIn(['students.view', 'templates.view', 'templates.manage', 'lookups.manage', 'users.manage', 'activity.view']);
    fakeLookups();
});

it('builds a template from scratch: add, reorder, style, hide, save', function () {
    fakeSources();
    Http::fake([API.'/templates' => Http::response(['data' => ['id' => 4, 'name' => 'كشف الرسوم']], 201)]);

    Livewire::test(Builder::class)
        ->set('name', 'كشف الرسوم')
        ->call('addColumn', 'student.name')
        ->call('addColumn', 'student.national_id')
        ->call('addColumn', 'row_number')
        ->call('sortColumn', 2, 0)                       // drag "م" to the top
        ->assertSet('columns.0.source', 'row_number')
        ->assertSet('columns.0.align', 'center')
        ->call('selectColumn', 1)
        ->set('columns.1.label', 'الاسم')
        ->set('columns.1.bold', true)
        ->set('columns.1.bg_color', '#FFFF00')
        ->call('toggleVisible', 2)
        ->set('settings.title_row', 'كشف الرسوم')
        ->set('settings.orientation', 'portrait')
        ->call('save')
        ->assertRedirect(route('templates.index'));

    Http::assertSent(function (Request $r) {
        if ($r->url() !== API.'/templates' || $r->method() !== 'POST') {
            return false;
        }

        return $r['name'] === 'كشف الرسوم'
            && array_column($r['columns'], 'source') === ['row_number', 'student.name', 'student.national_id']
            && $r['columns'][1]['label'] === 'الاسم' && $r['columns'][1]['bold'] === true && $r['columns'][1]['bg_color'] === '#FFFF00'
            && $r['columns'][2]['is_visible'] === false
            && $r['settings']['orientation'] === 'portrait' && $r['settings']['rtl'] === true;
    });
});

it('loads an existing template and shows column errors from the server', function () {
    fakeSources();
    Http::fake([API.'/templates/4' => Http::sequence()
        ->push(['data' => ['id' => 4, 'name' => 'قديم', 'settings' => ['rtl' => true, 'paper' => 'A3'],
            'columns' => [['id' => 1, 'order' => 1, 'label' => 'م', 'source' => 'row_number', 'width' => 6, 'align' => 'center',
                'font_size' => 11, 'bold' => false, 'bg_color' => null, 'text_color' => null, 'format' => 'number', 'is_visible' => true]]]])
        ->push(['message' => 'البيانات المدخلة غير صحيحة.', 'errors' => ['columns.0.label' => ['حقل اسم العمود مطلوب.']], 'code' => 'validation_error'], 422)]);

    Livewire::test(Builder::class, ['template' => 4])
        ->assertSet('name', 'قديم')
        ->assertSet('settings.paper', 'A3')
        ->assertSet('columns.0.label', 'م')
        ->set('columns.0.label', '')
        ->call('save')
        ->assertHasErrors('columns.0.label')
        ->assertSet('tab', 'columns')
        ->assertSee('حقل اسم العمود مطلوب.');
});

it('previews the template with sample students', function () {
    fakeSources();
    Http::fake([API.'/templates/preview' => Http::response(['data' => [
        'settings' => ['rtl' => true, 'title_row' => 'كشف', 'header_bg' => '#1F4E78', 'header_text_color' => '#FFFFFF', 'borders' => true, 'font_family' => 'Arial'],
        'columns' => [['label' => 'م', 'source' => 'row_number', 'width' => 6, 'align' => 'center', 'font_size' => 11, 'bold' => false, 'bg_color' => null, 'text_color' => null, 'format' => 'number', 'is_visible' => true]],
        'rows' => [['1'], ['2']],
    ]])]);

    Livewire::test(Builder::class)
        ->call('addColumn', 'row_number')
        ->set('tab', 'preview')
        ->assertSee('كشف')
        ->assertSeeHtml('background: #1F4E78');

    Http::assertSent(fn (Request $r) => $r->url() === API.'/templates/preview' && $r['columns'][0]['source'] === 'row_number');
});

it('downloads a sample file of an unsaved template (browser fallback)', function () {
    fakeSources();
    Http::fake([
        API.'/templates/sample' => Http::response(['data' => ['url' => 'http://api.test/api/v1/files/template-samples/abc?signature=x', 'expires_at' => 'x', 'file_name' => 'تجربة.xlsx']]),
        API.'/files/template-samples/*' => Http::response('PK-bytes'),
    ]);

    $component = Livewire::test(Builder::class)->call('addColumn', 'student.name')->call('sample');

    $this->get($component->effects['redirect'])->assertOk()->assertDownload();
});

it('lists templates with default, duplicate and delete actions', function () {
    Http::fake([
        API.'/templates/2/default' => Http::response(['data' => ['id' => 2]]),
        API.'/templates/2/duplicate' => Http::response(['data' => ['id' => 9]], 201),
        API.'/templates' => Http::response(['data' => [
            ['id' => 1, 'name' => 'الكشف الافتراضي', 'is_default' => true, 'columns_count' => 10],
            ['id' => 2, 'name' => 'قالب آخر', 'is_default' => false, 'columns_count' => 3],
        ]]),
    ]);

    Livewire::test(TemplatesIndex::class)
        ->assertSee('الكشف الافتراضي')->assertSee('10 عمود')
        ->call('makeDefault', 2)
        ->assertSee('أصبح هذا القالب هو الافتراضي.')
        ->call('duplicate', 2)
        ->assertRedirect(route('templates.edit', 9));
});

it('manages lookups and shows the in-use error', function () {
    Http::fake([
        API.'/case_types/2' => Http::response(['message' => 'لا يمكن الحذف لأن هذا العنصر مستخدم.', 'errors' => [], 'code' => 'in_use'], 409),
        API.'/case_types' => Http::response(['data' => ['id' => 7, 'name' => 'منحة', 'color' => '#10B981', 'is_active' => true]], 201),
    ]);

    Livewire::test(LookupsIndex::class)
        ->assertSee('معهد النور')
        ->set('tab', 'case_types')
        ->assertSee('لم يدفع')
        ->call('create')
        ->set('form.name', 'منحة')
        ->set('form.color', '#10B981')
        ->call('save')
        ->assertSet('editing', null)
        ->call('delete', 2)
        ->assertSee('لا يمكن الحذف لأن هذا العنصر مستخدم.');

    Http::assertSent(fn (Request $r) => $r->url() === API.'/case_types' && $r['name'] === 'منحة' && $r['is_active'] === true);
});

it('maps lookup field errors to the form', function () {
    Http::fake([API.'/institutes' => Http::response([
        'message' => 'البيانات المدخلة غير صحيحة.', 'errors' => ['name' => ['قيمة الاسم مستخدمة من قبل.']], 'code' => 'validation_error'], 422)]);

    Livewire::test(LookupsIndex::class)
        ->call('create')->set('form.name', 'معهد النور')->call('save')
        ->assertHasErrors('form.name')
        ->assertSee('قيمة الاسم مستخدمة من قبل.');
});

it('uploads the institute logo', function () {
    Http::fake([API.'/institutes/1/logo' => Http::response(['data' => ['id' => 1, 'name' => 'معهد النور', 'has_logo' => true]])]);

    Livewire::test(Logo::class)
        ->set('logo', UploadedFile::fake()->image('logo.png', 100, 100))
        ->call('upload')
        ->assertHasNoErrors()
        ->assertSee('تم رفع الشعار.');

    Http::assertSent(fn (Request $r) => $r->url() === API.'/institutes/1/logo' && $r->method() === 'POST' && $r->isMultipart());
});

it('creates and edits users', function () {
    Http::fake([
        API.'/users/3' => Http::sequence()
            ->push(['data' => ['id' => 3, 'name' => 'منى', 'email' => 'mona@school.test', 'is_active' => true, 'role' => ['value' => 'staff', 'label' => 'موظف'], 'institute' => ['id' => 1, 'name' => 'معهد النور']]])
            ->push(['data' => ['id' => 3]]),
        API.'/users*' => Http::response(apiPage([['id' => 3, 'name' => 'منى', 'email' => 'mona@school.test', 'is_active' => false, 'role' => ['value' => 'staff', 'label' => 'موظف'], 'institute' => null]])),
    ]);

    Livewire::test(UsersIndex::class)->assertSee('منى')->assertSee('موقوف');

    Livewire::test(UserForm::class, ['user' => 3])
        ->assertSet('email', 'mona@school.test')
        ->set('role', 'admin')
        ->set('is_active', false)
        ->call('save')
        ->assertRedirect(route('users.index'));

    Http::assertSent(fn (Request $r) => $r->method() === 'PUT' && $r['role'] === 'admin' && $r['institute_id'] === 1
        && $r['is_active'] === false && ! array_key_exists('password', array_filter($r->data(), fn ($v) => $v !== null)));
});

it('sends no institute for super admins', function () {
    Http::fake([API.'/users' => Http::response(['data' => ['id' => 5]], 201)]);

    Livewire::test(UserForm::class)
        ->set('name', 'مدير')->set('email', 'BOSS@school.test')->set('password', 'secret123')
        ->set('role', 'super_admin')
        ->call('save');

    Http::assertSent(fn (Request $r) => $r->url() === API.'/users' && $r['institute_id'] === null && $r['email'] === 'boss@school.test');
});

it('shows the dashboard', function () {
    Http::fake([API.'/dashboard' => Http::response(['data' => [
        'students' => ['total' => 107, 'active' => 98, 'inactive' => 9],
        'guardians' => 80,
        'reports' => ['total' => 12, 'this_month' => 3],
        'students_per_case_type' => [['case_type' => ['id' => 2, 'name' => 'لم يدفع', 'color' => '#DC2626'], 'students' => 25]],
        'reports_per_month' => collect(range(1, 12))->map(fn ($i) => ['month' => sprintf('2026-%02d', $i), 'label' => 'x', 'count' => $i % 3])->all(),
        'latest_reports' => [apiReport(5)],
    ]])]);

    Livewire::test(Dashboard::class)
        ->assertSee('107')->assertSee('98 نشط')->assertSee('لم يدفع')->assertSee('كشف الطلاب - لم يدفع')
        ->assertSeeHtml('background: #DC2626');
});

it('lists activity with filters', function () {
    Http::fake([API.'/activity*' => Http::response(apiPage([[
        'id' => 1, 'event' => 'updated', 'event_label' => 'عدّل', 'description' => 'منى عدّل طالب: أحمد علي',
        'subject' => ['type' => 'student', 'type_label' => 'طالب', 'id' => 4, 'label' => 'أحمد علي'],
        'causer' => ['id' => 2, 'name' => 'منى'],
        'changes' => ['old' => ['name' => 'أحمد'], 'attributes' => ['name' => 'أحمد علي']],
        'created_at' => '2026-10-14T10:00:00+03:00',
    ]]))]);

    Livewire::test(ActivityIndex::class)
        ->assertSee('منى عدّل طالب: أحمد علي')
        ->set('subjectType', 'student');

    Http::assertSent(fn (Request $r) => str_starts_with($r->url(), API.'/activity') && ($r->data()['subject_type'] ?? null) === 'student');
});

it('guards admin screens by permission', function () {
    signIn(['students.view', 'reports.view']);
    Http::fake([API.'/*' => Http::response(['data' => []])]);

    foreach (['/templates', '/templates/create', '/lookups', '/institute-logo', '/users', '/users/create', '/activity'] as $path) {
        $this->get($path)->assertForbidden();
    }
});
