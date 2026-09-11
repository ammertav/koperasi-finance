<?php

use App\Models\Account;
use App\Models\DocumentSequence;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\JournalTemplate;
use App\Models\JournalTemplateLine;
use App\Models\Office;
use App\Models\Scopes\OfficeScope;
use App\Services\Accounting\InterOfficeAccountService;
use App\Services\Accounting\JournalService;
use Database\Seeders\AccountSeeder;
use Database\Seeders\JournalTemplateSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([AccountSeeder::class, JournalTemplateSeeder::class]);
    $this->journalService = app(JournalService::class);
});

describe('post', function () {
    it('posts a balanced journal on the office book date with an office-coded number', function () {
        $this->travelTo('2026-09-20 10:00:00');
        $branch = Office::factory()->create(['code' => '03', 'book_date' => '2026-09-11']);

        $journal = $this->journalService->post($branch, [
            ['account_id' => accountId('1.1.01'), 'debit' => '500000'],
            ['account_id' => accountId('2.1.01'), 'credit' => 500000],
        ], 'Setoran simpanan sukarela', 'savings_deposit');

        expect($journal->number)->toBe('JU-03-20260911-0001')
            ->and($journal->book_date->toDateString())->toBe('2026-09-11')
            ->and($journal->status)->toBe('posted')
            ->and($journal->total_amount)->toBe('500000.00')
            ->and($journal->transaction_type)->toBe('savings_deposit');

        expect(JournalDetail::where('journal_id', $journal->id)->get()->map(fn (JournalDetail $detail) => [
            $detail->office_id,
            $detail->book_date->toDateString(),
        ])->unique()->values()->all())->toBe([[$branch->id, '2026-09-11']]);

        expect(journalLines($journal))->toBe([
            ['1.1.01', '500000.00', '0.00'],
            ['2.1.01', '0.00', '500000.00'],
        ]);
    });

    it('numbers journals sequentially per office and book date', function () {
        $branch03 = Office::factory()->create(['code' => '03', 'book_date' => '2026-09-11']);
        $branch07 = Office::factory()->create(['code' => '07', 'book_date' => '2026-09-11']);
        $lines = [
            ['account_id' => accountId('1.1.01'), 'debit' => '1000'],
            ['account_id' => accountId('2.1.01'), 'credit' => '1000'],
        ];

        $numbers[] = $this->journalService->post($branch03, $lines, 'Pertama')->number;
        $numbers[] = $this->journalService->post($branch03, $lines, 'Kedua')->number;
        $numbers[] = $this->journalService->post($branch07, $lines, 'Kantor lain')->number;
        $branch03->update(['book_date' => '2026-09-12']);
        $numbers[] = $this->journalService->post($branch03, $lines, 'Hari berikutnya')->number;

        expect($numbers)->toBe([
            'JU-03-20260911-0001',
            'JU-03-20260911-0002',
            'JU-07-20260911-0001',
            'JU-03-20260912-0001',
        ]);
    });

    it('rejects an unbalanced journal without saving anything', function () {
        $branch = Office::factory()->create();

        expect(fn () => $this->journalService->post($branch, [
            ['account_id' => accountId('1.1.01'), 'debit' => '500000'],
            ['account_id' => accountId('2.1.01'), 'credit' => '400000'],
        ], 'Tidak seimbang'))->toThrow(DomainException::class, 'Jurnal tidak seimbang: debit Rp 500.000 dan kredit Rp 400.000.');

        expect(Journal::count())->toBe(0)
            ->and(JournalDetail::count())->toBe(0)
            ->and(DocumentSequence::count())->toBe(0);
    });

    it('rejects invalid journal lines', function (array $lines, string $message) {
        $branch = Office::factory()->create();

        expect(fn () => $this->journalService->post($branch, $lines, 'Jurnal uji'))
            ->toThrow(DomainException::class, $message);

        expect(Journal::count())->toBe(0);
    })->with([
        'a single line' => [
            fn () => [['account_id' => accountId('1.1.01'), 'debit' => '1000']],
            'Jurnal minimal terdiri dari dua baris.',
        ],
        'a header account' => [
            fn () => [['account_id' => accountId('1.1'), 'debit' => '1000'], ['account_id' => accountId('2.1.01'), 'credit' => '1000']],
            'Akun 1.1 Kas dan Bank adalah akun induk dan tidak dapat diposting.',
        ],
        'an amount with cents' => [
            fn () => [['account_id' => accountId('1.1.01'), 'debit' => '1000.50'], ['account_id' => accountId('2.1.01'), 'credit' => '1000.50']],
            'Nominal Rp 1000.50 harus dibulatkan ke rupiah penuh.',
        ],
        'a negative amount' => [
            fn () => [['account_id' => accountId('1.1.01'), 'debit' => '-1000'], ['account_id' => accountId('2.1.01'), 'credit' => '-1000']],
            'Nominal jurnal tidak boleh negatif.',
        ],
        'a line with both debit and credit' => [
            fn () => [['account_id' => accountId('1.1.01'), 'debit' => '1000', 'credit' => '1000'], ['account_id' => accountId('2.1.01'), 'credit' => '1000']],
            'Baris akun 1.1.01 harus berisi debit atau kredit saja.',
        ],
        'a zero line' => [
            fn () => [['account_id' => accountId('1.1.01'), 'debit' => '0'], ['account_id' => accountId('2.1.01'), 'credit' => '0']],
            'Baris akun 1.1.01 harus berisi debit atau kredit saja.',
        ],
        'a head office only account at a branch' => [
            fn () => [['account_id' => accountId('1.1.03'), 'debit' => '1000'], ['account_id' => accountId('3.2.01'), 'credit' => '1000']],
            'Akun 3.2.01 Cadangan Umum hanya boleh dipakai kantor pusat.',
        ],
    ]);

    it('rejects an inactive account', function () {
        $branch = Office::factory()->create();
        Account::where('code', '2.1.02')->update(['is_active' => false]);

        expect(fn () => $this->journalService->post($branch, [
            ['account_id' => accountId('1.1.01'), 'debit' => '1000'],
            ['account_id' => accountId('2.1.02'), 'credit' => '1000'],
        ], 'Akun nonaktif'))->toThrow(DomainException::class, 'Akun 2.1.02 Simpanan Berjangka tidak aktif.');
    });

    it('rejects posting to an office that is not active', function () {
        $branch = Office::factory()->create(['name' => 'Cabang Tutup', 'is_active' => false]);

        expect(fn () => $this->journalService->post($branch, [
            ['account_id' => accountId('1.1.01'), 'debit' => '1000'],
            ['account_id' => accountId('2.1.01'), 'credit' => '1000'],
        ], 'Kantor nonaktif'))->toThrow(DomainException::class, 'Kantor Cabang Tutup tidak aktif.');
    });

    it('rejects a manual line on a RAK account', function () {
        $headOffice = Office::factory()->headOffice()->create(['code' => '00']);
        $branch = Office::factory()->create(['code' => '03', 'name' => 'Cabang Garut']);
        app(InterOfficeAccountService::class)->ensureAccountsFor($branch);

        expect(fn () => $this->journalService->post($headOffice, [
            ['account_id' => accountId('1.9.03'), 'debit' => '1000'],
            ['account_id' => accountId('1.1.03'), 'credit' => '1000'],
        ], 'RAK manual'))->toThrow(DomainException::class, 'Akun 1.9.03 RAK Cabang Garut hanya dibentuk otomatis oleh transaksi antar kantor.');
    });

    it('forbids updating or deleting a posted journal and its lines', function () {
        $branch = Office::factory()->create();
        $journal = $this->journalService->post($branch, [
            ['account_id' => accountId('1.1.01'), 'debit' => '1000'],
            ['account_id' => accountId('2.1.01'), 'credit' => '1000'],
        ], 'Asli');
        $detail = JournalDetail::where('journal_id', $journal->id)->first();

        expect(fn () => $journal->update(['description' => 'Diubah']))
            ->toThrow(LogicException::class, 'Jurnal yang sudah diposting tidak dapat diubah. Gunakan jurnal pembalik.')
            ->and(fn () => $journal->delete())
            ->toThrow(LogicException::class, 'Jurnal yang sudah diposting tidak dapat dihapus. Gunakan jurnal pembalik.')
            ->and(fn () => $detail->update(['debit' => '2000']))
            ->toThrow(LogicException::class, 'Baris jurnal yang sudah diposting tidak dapat diubah atau dihapus.')
            ->and(fn () => $detail->delete())
            ->toThrow(LogicException::class, 'Baris jurnal yang sudah diposting tidak dapat diubah atau dihapus.');

        expect($journal->fresh()->description)->toBe('Asli')
            ->and(journalLines($journal))->toBe([
                ['1.1.01', '1000.00', '0.00'],
                ['2.1.01', '0.00', '1000.00'],
            ]);
    });
});

describe('postFromTemplate', function () {
    it('builds the journal from the product template', function () {
        $branch = Office::factory()->create();

        $journal = $this->journalService->postFromTemplate('loan_disbursement', 'PUM', $branch, null, [
            'principal' => '35000000',
            'net_disbursement' => '34600000',
            'provision_fee' => '350000',
            'admin_fee' => '50000',
        ], 'Pencairan pinjaman');

        expect($journal->transaction_type)->toBe('loan_disbursement')
            ->and(journalLines($journal))->toBe([
                ['1.2.01', '35000000.00', '0.00'],
                ['1.1.01', '0.00', '34600000.00'],
                ['4.2.01', '0.00', '350000.00'],
                ['4.2.02', '0.00', '50000.00'],
            ]);
    });

    it('skips template lines whose amount is zero', function () {
        $branch = Office::factory()->create();

        $journal = $this->journalService->postFromTemplate('loan_installment', 'PUS', $branch, $branch, [
            'total_payment' => '1500000',
            'principal' => '1000000',
            'interest' => '500000',
            'penalty' => '0',
        ], 'Angsuran tanpa denda');

        expect(journalLines($journal))->toBe([
            ['1.1.01', '1500000.00', '0.00'],
            ['1.2.02', '0.00', '1000000.00'],
            ['4.1.02', '0.00', '500000.00'],
        ]);
    });

    it('prefers the product template over the generic template of the same transaction type', function () {
        $branch = Office::factory()->create();
        $genericTemplate = JournalTemplate::create([
            'code' => 'SAVINGS_DEPOSIT_GENERIC',
            'transaction_type' => 'savings_deposit',
            'product_code' => null,
            'name' => 'Setoran titipan',
        ]);
        JournalTemplateLine::create(['journal_template_id' => $genericTemplate->id, 'account_id' => accountId('1.1.01'), 'side' => 'debit', 'amount_key' => 'amount']);
        JournalTemplateLine::create(['journal_template_id' => $genericTemplate->id, 'account_id' => accountId('2.2.01'), 'side' => 'credit', 'amount_key' => 'amount']);

        $productJournal = $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '1000'], 'Produk SS');
        $genericJournal = $this->journalService->postFromTemplate('savings_deposit', 'XX', $branch, null, ['amount' => '1000'], 'Produk tanpa template');

        expect(journalLines($productJournal)[1][0])->toBe('2.1.01')
            ->and(journalLines($genericJournal)[1][0])->toBe('2.2.01');
    });

    it('rejects a transaction without a template', function () {
        $branch = Office::factory()->create();

        expect(fn () => $this->journalService->postFromTemplate('savings_withdrawal', 'SP', $branch, null, ['amount' => '1000'], 'Tarik pokok'))
            ->toThrow(DomainException::class, 'Template jurnal untuk transaksi Penarikan simpanan produk SP belum tersedia.');
    });

    it('rejects a missing template amount', function () {
        $branch = Office::factory()->create();

        expect(fn () => $this->journalService->postFromTemplate('loan_installment', 'PUM', $branch, null, [
            'total_payment' => '1500000',
            'principal' => '1000000',
            'interest' => '500000',
        ], 'Angsuran'))->toThrow(DomainException::class, 'Nominal "penalty" wajib diisi untuk template LOAN_INSTALLMENT_PUM.');

        expect(Journal::count())->toBe(0);
    });
});

describe('postInterOffice', function () {
    it('posts RAK journals at the head office and the branch for a fund dropping', function () {
        $headOffice = Office::factory()->headOffice()->create(['code' => '00']);
        $branch = Office::factory()->create(['code' => '03', 'parent_id' => $headOffice->id]);

        $journal = $this->journalService->postFromTemplate('fund_dropping', null, $headOffice, $branch, ['amount' => '150000000'], 'Dropping dana');

        $journals = Journal::where('inter_office_group', $journal->inter_office_group)->orderBy('id')->get();

        expect($journals->pluck('office_id')->all())->toBe([$headOffice->id, $branch->id])
            ->and($journal->is($journals[0]))->toBeTrue()
            ->and(journalLines($journals[0]))->toBe([
                ['1.1.03', '0.00', '150000000.00'],
                ['1.9.03', '150000000.00', '0.00'],
            ])
            ->and(journalLines($journals[1]))->toBe([
                ['1.1.02', '150000000.00', '0.00'],
                ['1.9.00', '0.00', '150000000.00'],
            ]);
    });

    it('routes a transaction between two branches through the head office', function () {
        $headOffice = Office::factory()->headOffice()->create(['code' => '00']);
        $branch03 = Office::factory()->create(['code' => '03']);
        $branch07 = Office::factory()->create(['code' => '07']);

        // Anggota Cabang 07 menyetor simpanan sukarela di Cabang 03.
        $journal = $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch03, $branch07, ['amount' => '2000000'], 'Setoran di cabang lain');

        $journals = Journal::where('inter_office_group', $journal->inter_office_group)->orderBy('id')->get();

        expect($journals->pluck('office_id')->all())->toBe([$branch03->id, $headOffice->id, $branch07->id])
            ->and(journalLines($journals[0]))->toBe([
                ['1.1.01', '2000000.00', '0.00'],
                ['1.9.00', '0.00', '2000000.00'],
            ])
            ->and(journalLines($journals[1]))->toBe([
                ['1.9.03', '2000000.00', '0.00'],
                ['1.9.07', '0.00', '2000000.00'],
            ])
            ->and(journalLines($journals[2]))->toBe([
                ['2.1.01', '0.00', '2000000.00'],
                ['1.9.00', '2000000.00', '0.00'],
            ]);
    });

    it('saves no journal in any office when one office cannot post', function () {
        Office::factory()->headOffice()->create(['code' => '00', 'name' => 'Kantor Pusat', 'is_active' => false]);
        $branch03 = Office::factory()->create(['code' => '03']);
        $branch07 = Office::factory()->create(['code' => '07']);

        expect(fn () => $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch03, $branch07, ['amount' => '2000000'], 'Setoran'))
            ->toThrow(DomainException::class, 'Kantor Kantor Pusat tidak aktif.');

        expect(Journal::count())->toBe(0)
            ->and(JournalDetail::count())->toBe(0);
    });

    it('rejects lines that do not balance across both offices', function () {
        Office::factory()->headOffice()->create();
        $branch03 = Office::factory()->create();
        $branch07 = Office::factory()->create();

        expect(fn () => $this->journalService->postInterOffice(
            $branch03,
            [['account_id' => accountId('1.1.01'), 'debit' => '1000']],
            $branch07,
            [['account_id' => accountId('2.1.01'), 'credit' => '900']],
            'Tidak seimbang',
        ))->toThrow(DomainException::class, 'Jurnal antar kantor tidak seimbang: selisih kantor asal Rp 1.000 dan kantor tujuan Rp -900.');

        expect(Journal::count())->toBe(0);
    });

    it('rejects the same office as origin and destination', function () {
        $branch = Office::factory()->create();

        expect(fn () => $this->journalService->postInterOffice($branch, [], $branch, [], 'Kantor sama'))
            ->toThrow(DomainException::class, 'Kantor asal dan kantor tujuan transaksi antar kantor harus berbeda.');
    });

    it('posts every office leg when a branch user is logged in', function () {
        $this->seed(RoleSeeder::class);
        Office::factory()->headOffice()->create();
        $branch03 = Office::factory()->create();
        $branch07 = Office::factory()->create();
        $teller = userWithRole('TLR', $branch03);
        $this->actingAs($teller);

        $journal = $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch03, $branch07, ['amount' => '2000000'], 'Setoran');

        expect(Journal::withoutGlobalScope(OfficeScope::class)->where('inter_office_group', $journal->inter_office_group)->count())->toBe(3)
            ->and($journal->created_by)->toBe($teller->id);
    });

    it('does not create RAK accounts between two branches', function () {
        Office::factory()->headOffice()->create();
        $branch03 = Office::factory()->create();
        $branch07 = Office::factory()->create();

        expect(fn () => app(InterOfficeAccountService::class)->accountFor($branch03, $branch07))
            ->toThrow(DomainException::class, 'Rekening Antar Kantor hanya dibentuk untuk pasangan kantor pusat dan cabang.');
    });
});

describe('reverse', function () {
    it('reverses a journal with swapped lines on the current book date', function () {
        $branch = Office::factory()->create(['code' => '03', 'book_date' => '2026-09-11']);
        $journal = $this->journalService->post($branch, [
            ['account_id' => accountId('1.1.01'), 'debit' => '750000'],
            ['account_id' => accountId('2.1.01'), 'credit' => '750000'],
        ], 'Setoran');
        $branch->update(['book_date' => '2026-09-14']);

        $reversal = $this->journalService->reverse($journal, 'Salah input nominal');

        expect($reversal->reversal_of_id)->toBe($journal->id)
            ->and($reversal->transaction_type)->toBe('reversal')
            ->and($reversal->book_date->toDateString())->toBe('2026-09-14')
            ->and($reversal->number)->toBe('JU-03-20260914-0001')
            ->and($reversal->description)->toBe('Pembalikan JU-03-20260911-0001: Salah input nominal')
            ->and(journalLines($reversal))->toBe([
                ['1.1.01', '0.00', '750000.00'],
                ['2.1.01', '750000.00', '0.00'],
            ]);

        expect($journal->fresh()->book_date->toDateString())->toBe('2026-09-11');
    });

    it('refuses to reverse a journal twice or to reverse a reversal', function () {
        $branch = Office::factory()->create();
        $journal = $this->journalService->post($branch, [
            ['account_id' => accountId('1.1.01'), 'debit' => '1000'],
            ['account_id' => accountId('2.1.01'), 'credit' => '1000'],
        ], 'Setoran');
        $reversal = $this->journalService->reverse($journal, 'Koreksi');

        expect(fn () => $this->journalService->reverse($journal, 'Koreksi lagi'))
            ->toThrow(DomainException::class, "Jurnal {$journal->number} sudah dibalik.")
            ->and(fn () => $this->journalService->reverse($reversal, 'Balik pembalik'))
            ->toThrow(DomainException::class, 'Jurnal pembalik tidak dapat dibalik lagi.');

        expect(Journal::count())->toBe(2);
    });

    it('reverses every journal of an inter-office transaction together', function () {
        Office::factory()->headOffice()->create(['code' => '00']);
        $branch03 = Office::factory()->create(['code' => '03']);
        $branch07 = Office::factory()->create(['code' => '07']);
        $journal = $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch03, $branch07, ['amount' => '2000000'], 'Setoran');
        $originalIds = Journal::where('inter_office_group', $journal->inter_office_group)->orderBy('id')->pluck('id')->all();

        $reversal = $this->journalService->reverse($journal, 'Salah cabang tujuan');

        $reversals = Journal::where('transaction_type', 'reversal')->orderBy('id')->get();

        expect($reversal->reversal_of_id)->toBe($journal->id)
            ->and($reversals->pluck('reversal_of_id')->all())->toBe($originalIds)
            ->and($reversals->pluck('inter_office_group')->unique()->count())->toBe(1)
            ->and($reversals[0]->inter_office_group)->not->toBe($journal->inter_office_group)
            ->and(journalLines($reversals[1]))->toBe([
                ['1.9.03', '0.00', '2000000.00'],
                ['1.9.07', '2000000.00', '0.00'],
            ]);
    });
});
