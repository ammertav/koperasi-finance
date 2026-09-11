<?php

use App\Models\Office;
use App\Services\Accounting\JournalService;
use Database\Seeders\AccountSeeder;
use Database\Seeders\JournalTemplateSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed([RoleSeeder::class, AccountSeeder::class, JournalTemplateSeeder::class]);
});

it('renders the chart of accounts and journal templates', function () {
    $accountingStaff = userWithRole('SAK');

    $this->actingAs($accountingStaff)
        ->get(route('account'))
        ->assertSee(['Kas Teller', 'Simpanan Pokok']);

    $this->actingAs($accountingStaff)
        ->get(route('journalTemplate'))
        ->assertSee('Pencairan Pinjaman Umum dengan potongan provisi dan administrasi');
});

it('renders a balanced consolidated trial balance for head office roles', function () {
    $headOffice = Office::factory()->headOffice()->create(['code' => '00']);
    $branch = Office::factory()->create(['code' => '03']);
    app(JournalService::class)->postFromTemplate('fund_dropping', null, $headOffice, $branch, ['amount' => '150000000'], 'Dropping');

    $this->actingAs(userWithRole('MGR', $headOffice))
        ->get(route('trialBalance'))
        ->assertSee(['Konsolidasi semua kantor', 'Seimbang', 'Kas Brankas', 'Saldo RAK saling menghapus pada konsolidasi']);
});

it('renders the general ledger of an account with its running balance', function () {
    $branch = Office::factory()->create();
    app(JournalService::class)->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '1250000'], 'Setoran sukarela');

    $this->actingAs(userWithRole('KCB', $branch))
        ->get(route('generalLedger', ['account_id' => accountId('2.1.01')]))
        ->assertSee(['2.1.01 — Simpanan Sukarela', 'Rp 1.250.000']);
});

it('returns 404 when a branch role requests the trial balance of another office', function () {
    $ownBranch = Office::factory()->create();
    $otherBranch = Office::factory()->create();

    $this->actingAs(userWithRole('KCB', $ownBranch))
        ->get(route('trialBalance', ['office_id' => $otherBranch->id]))
        ->assertNotFound();
});
