<?php

use App\Livewire\Reports\Index;
use App\Livewire\Reports\Show;
use App\Livewire\Reports\Wizard;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    signIn(['students.view', 'templates.view', 'reports.view', 'reports.create', 'reports.download', 'reports.delete']);
    fakeLookups();
});

it('walks through the wizard, selects all filtered students and generates', function () {
    Http::fake([
        API.'/templates' => Http::response(['data' => [
            ['id' => 1, 'name' => 'الكشف الافتراضي', 'is_default' => true],
            ['id' => 2, 'name' => 'قالب آخر', 'is_default' => false],
        ]]),
        API.'/students/ids*' => Http::response(['data' => ['ids' => [1, 2, 3], 'count' => 3, 'truncated' => false]]),
        API.'/students*' => Http::response(apiPage([apiStudent(1), apiStudent(2)], 1, 2, 3)),
        API.'/reports' => Http::response(['data' => apiReport(5, 'pending')], 201),
    ]);

    Livewire::test(Wizard::class)
        ->assertSet('templateId', 1)
        ->call('next')                       // → case type
        ->assertSee('لم يدفع')->assertDontSee('قديم')   // inactive case types hidden
        ->set('caseTypeId', '2')
        ->call('next')                       // → students
        ->assertSee('طالب 1')
        ->call('next')                       // nothing selected yet
        ->assertHasErrors('selected')
        ->assertSet('step', 3)
        ->call('toggle', 2)
        ->call('selectAllFiltered')
        ->assertSet('selected', [2, 1, 3])
        ->call('next')                       // → review
        ->assertSee('و 1 آخرين')
        ->set('title', 'كشف أكتوبر')
        ->call('generate')
        ->assertRedirect(route('reports.show', 5));

    Http::assertSent(fn (Request $r) => $r->method() === 'POST' && $r->url() === API.'/reports'
        && $r['template_id'] === 1 && $r['case_type_id'] === 2 && $r['student_ids'] === [2, 1, 3] && $r['title'] === 'كشف أكتوبر');
    Http::assertSent(fn (Request $r) => str_starts_with($r->url(), API.'/students/ids') && $r->data()['status'] === 'active');
});

it('toggles students on and off', function () {
    Http::fake([API.'/templates' => Http::response(['data' => [['id' => 1, 'name' => 'x', 'is_default' => true]]])]);

    Livewire::test(Wizard::class)
        ->call('toggle', 4)->call('toggle', 5)->call('toggle', 4)
        ->assertSet('selected', [5])
        ->call('clearSelection')
        ->assertSet('selected', []);
});

it('lists reports and filters them', function () {
    Http::fake([API.'/reports*' => Http::response(apiPage([apiReport(5), apiReport(6, 'failed')]))]);

    Livewire::test(Index::class)
        ->assertSee('كشف الطلاب - لم يدفع')->assertSee('جاهز')->assertSee('فشل')
        ->set('status', 'ready');

    Http::assertSent(fn (Request $r) => str_starts_with($r->url(), API.'/reports') && ($r->data()['status'] ?? null) === 'ready');
});

it('polls a pending report until it is ready', function () {
    Http::fake([
        API.'/reports/5/students*' => Http::response(apiPage([['order' => 1, 'student' => apiStudent(1), 'case_type' => null]])),
        API.'/reports/5' => Http::sequence()->push(['data' => apiReport(5, 'pending')])->push(['data' => apiReport(5, 'ready')]),
    ]);

    Livewire::test(Show::class, ['report' => 5])
        ->assertSee('جاري إنشاء الملف')
        ->assertSeeHtml('wire:poll.2s="refreshStatus"')
        ->assertDontSee('مشاركة الملف')
        ->call('refreshStatus')
        ->assertSee('مشاركة الملف')
        ->assertDontSeeHtml('wire:poll')
        ->assertSee('طالب 1');
});

it('downloads the file through the signed link and offers it (browser fallback)', function () {
    Http::fake([
        API.'/reports/5/students*' => Http::response(apiPage([])),
        API.'/reports/5/download' => Http::response(['data' => [
            'url' => 'http://api.test/api/v1/files/reports/5?expires=1&signature=abc',
            'expires_at' => '2026-10-14T10:15:00+03:00',
            'file_name' => 'كشف الطلاب - 2026-10-14.xlsx',
        ]]),
        API.'/files/reports/5*' => Http::response('PK-xlsx-bytes'),
        API.'/reports/5' => Http::response(['data' => apiReport(5)]),
    ]);

    $component = Livewire::test(Show::class, ['report' => 5])->call('share');

    // The signed link is fetched without the bearer token.
    Http::assertSent(fn (Request $r) => str_contains($r->url(), 'signature=abc') && ! $r->hasHeader('Authorization'));

    $this->get($component->effects['redirect'])->assertOk()->assertDownload();
});

it('regenerates, duplicates and deletes', function () {
    Http::fake([
        API.'/reports/5/students*' => Http::response(apiPage([])),
        API.'/reports/5/regenerate' => Http::response(['data' => apiReport(5, 'pending')]),
        API.'/reports/5/duplicate' => Http::response(['data' => apiReport(8)], 201),
        API.'/reports/5' => Http::sequence()->push(['data' => apiReport(5)])->push(['data' => apiReport(5)])->push(null, 204),
    ]);

    Livewire::test(Show::class, ['report' => 5])
        ->call('regenerate')
        ->assertSet('record.status.value', 'pending')
        ->call('duplicate')
        ->assertRedirect(route('reports.show', 8));

    Livewire::test(Show::class, ['report' => 5])->call('delete')->assertRedirect(route('reports.index'));
});

it('hides actions the user may not use', function () {
    signIn(['reports.view']);
    Http::fake([
        API.'/reports/5/students*' => Http::response(apiPage([])),
        API.'/reports/5' => Http::response(['data' => apiReport(5)]),
    ]);

    Livewire::test(Show::class, ['report' => 5])
        ->assertDontSee('مشاركة الملف')
        ->assertDontSee('إعادة إنشاء الملف')
        ->assertDontSee('حذف الكشف');

    $this->get('/reports/create')->assertForbidden();
});
