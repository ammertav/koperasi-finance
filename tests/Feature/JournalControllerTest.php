<?php

use App\Models\Journal;
use App\Models\Office;
use App\Services\Accounting\JournalService;
use Database\Seeders\AccountSeeder;
use Database\Seeders\AuthorityLimitSeeder;
use Database\Seeders\JournalTemplateSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed([RoleSeeder::class, AuthorityLimitSeeder::class, AccountSeeder::class, JournalTemplateSeeder::class]);
});

describe('index', function () {
    it('renders journals of the book date for head office accounting staff', function () {
        $branch = Office::factory()->create();
        $journal = app(JournalService::class)->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '250000'], 'Setoran sukarela');

        $this->actingAs(userWithRole('SAK'))
            ->get(route('journal'))
            ->assertSee($journal->number);
    });

    it('forbids roles without accounting access', function () {
        $this->actingAs(userWithRole('TLR'))
            ->get(route('journal'))
            ->assertForbidden();
    });

    it('hides journals of other offices from branch roles', function () {
        $ownBranch = Office::factory()->create();
        $otherBranch = Office::factory()->create();
        $otherJournal = app(JournalService::class)->postFromTemplate('savings_deposit', 'SS', $otherBranch, null, ['amount' => '250000'], 'Setoran');
        $branchHead = userWithRole('KCB', $ownBranch);

        $this->actingAs($branchHead)
            ->get(route('journal'))
            ->assertDontSee($otherJournal->number);

        $this->actingAs($branchHead)
            ->get(route('detailJournal', $otherJournal->id))
            ->assertNotFound();
    });
});

describe('show', function () {
    it('renders the journal lines', function () {
        $branch = Office::factory()->create();
        $journal = app(JournalService::class)->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '250000'], 'Setoran sukarela');

        $this->actingAs(userWithRole('KAK'))
            ->get(route('detailJournal', $journal->id))
            ->assertSee(['Kas Teller', 'Simpanan Sukarela', 'Balik Jurnal']);
    });
});

describe('reverse', function () {
    it('creates a reversal journal authorized by the accounting head', function () {
        $branch = Office::factory()->create();
        $teller = userWithRole('TLR', $branch);
        $journal = app(JournalService::class)->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '250000'], 'Setoran', createdBy: $teller);
        $accountingHead = userWithRole('KAK');

        $response = $this->actingAs($accountingHead)
            ->post(route('reverseJournal', $journal->id), ['reason' => 'Salah input nominal']);

        $reversal = Journal::where('reversal_of_id', $journal->id)->firstOrFail();

        $response->assertRedirect(route('detailJournal', $reversal->id))
            ->assertSessionHas('success', 'Jurnal pembalik berhasil dibuat!');

        expect($reversal->created_by)->toBe($accountingHead->id)
            ->and($reversal->description)->toBe("Pembalikan {$journal->number}: Salah input nominal");
    });

    it('rejects a reversal by the creator of the journal', function () {
        $branch = Office::factory()->create();
        $accountingHead = userWithRole('KAK');
        $journal = app(JournalService::class)->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '250000'], 'Setoran', createdBy: $accountingHead);

        $this->actingAs($accountingHead)
            ->post(route('reverseJournal', $journal->id), ['reason' => 'Koreksi sendiri'])
            ->assertSessionHasErrors(['msg' => 'Pembuat jurnal tidak boleh membalik jurnalnya sendiri.']);

        expect(Journal::where('reversal_of_id', $journal->id)->exists())->toBeFalse();
    });

    it('rejects a journal that is already reversed', function () {
        $branch = Office::factory()->create();
        $journal = app(JournalService::class)->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '250000'], 'Setoran');
        app(JournalService::class)->reverse($journal, 'Koreksi pertama');

        $this->actingAs(userWithRole('KAK'))
            ->post(route('reverseJournal', $journal->id), ['reason' => 'Koreksi kedua'])
            ->assertSessionHasErrors(['msg' => "Jurnal {$journal->number} sudah dibalik."]);

        expect(Journal::where('transaction_type', 'reversal')->count())->toBe(1);
    });

    it('requires a reason', function () {
        $branch = Office::factory()->create();
        $journal = app(JournalService::class)->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '250000'], 'Setoran');

        $this->actingAs(userWithRole('KAK'))
            ->post(route('reverseJournal', $journal->id), [])
            ->assertSessionHasErrors(['reason']);

        expect(Journal::where('reversal_of_id', $journal->id)->exists())->toBeFalse();
    });

    it('forbids roles without accounting manage access', function () {
        $branch = Office::factory()->create();
        $journal = app(JournalService::class)->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '250000'], 'Setoran');

        $this->actingAs(userWithRole('KCB', $branch))
            ->post(route('reverseJournal', $journal->id), ['reason' => 'Koreksi'])
            ->assertForbidden();
    });
});
