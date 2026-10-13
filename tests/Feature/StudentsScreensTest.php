<?php

use App\Livewire\Students\Form;
use App\Livewire\Students\Import;
use App\Livewire\Students\Index;
use App\Livewire\Students\Show;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    signIn(['students.view', 'students.create', 'students.update', 'students.delete']);
    fakeLookups();
});

it('lists students and searches with Arabic digits', function () {
    Http::fake([API.'/students*' => Http::response(apiPage([apiStudent(1), apiStudent(2)], total: 2))]);

    Livewire::test(Index::class)
        ->assertSee('طالب 1')->assertSee('طالب 2')->assertSee('2 طالب')
        ->set('search', '٠١٠')
        ->set('gradeId', '3');

    Http::assertSent(fn (Request $r) => str_starts_with($r->url(), API.'/students')
        && ($r->data()['search'] ?? null) === '٠١٠' && ($r->data()['grade_id'] ?? null) === '3');
});

it('loads more students', function () {
    Http::fake([API.'/students*' => Http::sequence()
        ->push(apiPage([apiStudent(1)], 1, 2, 2))
        ->push(apiPage([apiStudent(2)], 2, 2, 2))]);

    Livewire::test(Index::class)
        ->assertSet('hasMore', true)
        ->call('loadMore')
        ->assertSet('hasMore', false)
        ->assertCount('students', 2);
});

it('shows a student with guardian and siblings, and deletes', function () {
    Http::fake([
        API.'/students/1' => Http::sequence()->push(['data' => apiStudent(1)])->push(null, 204),
        API.'/guardians/9' => Http::response(['data' => ['id' => 9, 'name' => 'علي حسن', 'phone' => '01012345678',
            'students' => [apiStudent(1), apiStudent(2, ['name' => 'أخت الطالب'])]]]),
    ]);

    Livewire::test(Show::class, ['student' => 1])
        ->assertSee('31201150101234')->assertSee('علي حسن')->assertSee('أخت الطالب')
        ->call('delete')
        ->assertRedirect(route('students.index'));

    Http::assertSent(fn (Request $r) => $r->method() === 'DELETE' && $r->url() === API.'/students/1');
});

it('fills birth date and gender from the national ID', function () {
    Livewire::test(Form::class)
        ->set('national_id', '٣١٢٠١١٥٠١٠١٢٤٣')   // 13th digit 4 → female
        ->assertSet('national_id', '31201150101243')
        ->assertSet('birth_date', '2012-01-15')
        ->assertSet('gender', 'female');
});

it('creates a student with a new guardian', function () {
    Http::fake([API.'/students' => Http::response(['data' => apiStudent(12)], 201)]);

    Livewire::test(Form::class)
        ->set('name', 'محمود علي')
        ->set('national_id', '31201150101234')
        ->set('grade_id', '3')
        ->set('address', 'المنصورة')
        ->set('guardianMode', 'new')
        ->set('guardian.name', 'علي حسن')
        ->set('guardian.phone', '٠١٠ ١٢٣٤ ٥٦٧٨')
        ->call('save')
        ->assertRedirect(route('students.show', 12));

    Http::assertSent(fn (Request $r) => $r->method() === 'POST' && $r->url() === API.'/students'
        && $r['grade_id'] === 3 && $r['gender'] === 'male' && $r['birth_date'] === '2012-01-15'
        && $r['guardian']['phone'] === '01012345678' && ! isset($r['guardian_id']));
});

it('picks an existing guardian (sibling)', function () {
    Http::fake([
        API.'/guardians*' => Http::response(apiPage([['id' => 9, 'name' => 'علي حسن', 'phone' => '01012345678', 'students_count' => 1]])),
        API.'/students' => Http::response(['data' => apiStudent(13)], 201),
    ]);

    Livewire::test(Form::class)
        ->set('guardianMode', 'existing')
        ->set('guardianSearch', '0101')
        ->assertSee('علي حسن')
        ->call('pickGuardian', 9)
        ->assertSet('guardian_id', 9)
        ->set('name', 'أخ')->set('national_id', '31201150101234')->set('grade_id', '3')->set('address', 'x')
        ->call('save');

    Http::assertSent(fn (Request $r) => $r->url() === API.'/students' && $r['guardian_id'] === 9 && ! isset($r['guardian']));
});

it('shows the server Arabic errors under the right fields', function () {
    Http::fake([API.'/students' => Http::response([
        'message' => 'البيانات المدخلة غير صحيحة.',
        'errors' => [
            'national_id' => ['حقل الرقم القومي يجب أن يكون رقمًا قوميًا صحيحًا.'],
            'guardian.phone' => ['حقل رقم تليفون ولي الأمر يجب أن يكون رقم موبايل مصري.'],
        ],
        'code' => 'validation_error',
    ], 422)]);

    Livewire::test(Form::class)
        ->set('guardianMode', 'new')
        ->call('save')
        ->assertHasErrors(['national_id', 'guardian.phone'])
        ->assertSee('حقل رقم تليفون ولي الأمر يجب أن يكون رقم موبايل مصري.')
        ->assertNoRedirect();
});

it('loads a student into the edit form and updates it', function () {
    Http::fake([API.'/students/1' => Http::sequence()
        ->push(['data' => apiStudent(1)])
        ->push(['data' => apiStudent(1, ['name' => 'معدل'])])]);

    Livewire::test(Form::class, ['student' => 1])
        ->assertSet('name', 'طالب 1')
        ->assertSet('guardianMode', 'existing')
        ->assertSet('guardian_id', 9)
        ->set('name', 'معدل')
        ->set('status', 'inactive')
        ->call('save')
        ->assertRedirect(route('students.show', 1));

    Http::assertSent(fn (Request $r) => $r->method() === 'PUT' && $r['name'] === 'معدل' && $r['status'] === 'inactive' && $r['guardian_id'] === 9);
});

it('imports an Excel file and lists failed rows', function () {
    signIn(['students.view', 'students.import']);
    Http::fake([API.'/students/import' => Http::response(['data' => [
        'imported' => 3, 'failed' => 1,
        'failed_rows' => [['row' => 5, 'errors' => ['الصف "العاشر" غير موجود في هذا المعهد.']]],
    ]])]);

    Livewire::test(Import::class)
        ->set('file', UploadedFile::fake()->create('students.xlsx', 20, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'))
        ->call('import')
        ->assertHasNoErrors()
        ->assertSee('تم استيراد 3 طالب.')
        ->assertSee('الصف رقم 5')
        ->assertSee('الصف "العاشر" غير موجود في هذا المعهد.');

    Http::assertSent(fn (Request $r) => $r->url() === API.'/students/import' && $r->isMultipart());
});

it('downloads the import template (browser fallback outside the app)', function () {
    signIn(['students.view', 'students.import']);
    Http::fake([API.'/students/import-template' => Http::response('xlsx-bytes')]);

    $component = Livewire::test(Import::class)->call('downloadTemplate');

    $redirect = $component->effects['redirect'];
    expect($redirect)->toContain('/files/');
    $this->get($redirect)->assertOk()->assertDownload();
});

it('guards student screens by permission', function () {
    signIn(['students.view']);
    Http::fake([API.'/*' => Http::response(apiPage([]))]);

    $this->get('/students/create')->assertForbidden();
    $this->get('/students/1/edit')->assertForbidden();
    $this->get('/students/import')->assertForbidden();
});
