<?php

use App\Models\Account;
use App\Models\Office;
use App\Services\Accounting\JournalService;
use App\Services\Accounting\LedgerService;
use Carbon\Carbon;
use Database\Seeders\AccountSeeder;
use Database\Seeders\JournalTemplateSeeder;

beforeEach(function () {
    $this->seed([AccountSeeder::class, JournalTemplateSeeder::class]);
    $this->journalService = app(JournalService::class);
    $this->ledgerService = app(LedgerService::class);
});

describe('trialBalance', function () {
    it('splits opening balances and period mutations per account', function () {
        $branch = Office::factory()->create(['book_date' => '2026-08-31']);
        $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '1000000'], 'Setoran Agustus');
        $branch->update(['book_date' => '2026-09-05']);
        $this->journalService->postFromTemplate('savings_withdrawal', 'SS', $branch, null, ['amount' => '300000'], 'Penarikan September');

        ['rows' => $rows, 'totals' => $totals] = $this->ledgerService->trialBalance($branch->id, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30'));

        expect($rows->map(fn (array $row) => [
            $row['account']->code,
            $row['opening_debit'],
            $row['opening_credit'],
            $row['debit'],
            $row['credit'],
            $row['closing_debit'],
            $row['closing_credit'],
        ])->all())->toBe([
            ['1.1.01', '1000000.00', '0.00', '0.00', '300000.00', '700000.00', '0.00'],
            ['2.1.01', '0.00', '1000000.00', '300000.00', '0.00', '0.00', '700000.00'],
        ]);

        expect($totals)->toBe([
            'opening_debit' => '1000000.00',
            'opening_credit' => '1000000.00',
            'debit' => '300000.00',
            'credit' => '300000.00',
            'closing_debit' => '700000.00',
            'closing_credit' => '700000.00',
        ]);
    });

    it('limits balances to the selected office and end date', function () {
        $branch = Office::factory()->create(['book_date' => '2026-09-11']);
        $otherBranch = Office::factory()->create(['book_date' => '2026-09-11']);
        $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '500000'], 'Dalam periode');
        $this->journalService->postFromTemplate('savings_deposit', 'SS', $otherBranch, null, ['amount' => '200000'], 'Kantor lain');
        $branch->update(['book_date' => '2026-09-12']);
        $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '100000'], 'Setelah periode');

        ['rows' => $rows] = $this->ledgerService->trialBalance($branch->id, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-11'));

        expect($rows->firstWhere('account.code', '1.1.01')['closing_debit'])->toBe('500000.00');
    });

    it('nets RAK balances to zero in the consolidated trial balance', function () {
        $headOffice = Office::factory()->headOffice()->create(['code' => '00']);
        $branch03 = Office::factory()->create(['code' => '03']);
        $branch07 = Office::factory()->create(['code' => '07']);
        $this->journalService->postFromTemplate('fund_dropping', null, $headOffice, $branch03, ['amount' => '150000000'], 'Dropping');
        $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch03, $branch07, ['amount' => '2000000'], 'Setoran di cabang lain');
        $period = [Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30')];

        $consolidated = $this->ledgerService->trialBalance(null, ...$period);
        $branchBalance = $this->ledgerService->trialBalance($branch03->id, ...$period);

        $consolidatedRak = $consolidated['rows']->filter(fn (array $row) => $row['account']->isInterOffice());
        $consolidatedRakNet = $consolidatedRak->reduce(
            fn (string $net, array $row) => bcadd($net, bcsub($row['closing_debit'], $row['closing_credit'], 2), 2),
            '0.00'
        );

        expect($consolidatedRak->map(fn (array $row) => [$row['account']->code, $row['closing_debit'], $row['closing_credit']])->values()->all())->toBe([
            ['1.9.00', '0.00', '150000000.00'],
            ['1.9.03', '152000000.00', '0.00'],
            ['1.9.07', '0.00', '2000000.00'],
        ])
            ->and($consolidatedRakNet)->toBe('0.00')
            ->and($consolidated['totals']['closing_debit'])->toBe($consolidated['totals']['closing_credit'])
            ->and($branchBalance['rows']->firstWhere('account.code', '1.9.00')['closing_credit'])->toBe('152000000.00');
    });
});

describe('generalLedger', function () {
    it('lists account mutations with a running balance in the normal balance direction', function () {
        $branch = Office::factory()->create(['book_date' => '2026-08-31']);
        $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '1000000'], 'Saldo Agustus');
        $branch->update(['book_date' => '2026-09-02']);
        $this->journalService->postFromTemplate('savings_deposit', 'SS', $branch, null, ['amount' => '500000'], 'Setoran');
        $branch->update(['book_date' => '2026-09-03']);
        $this->journalService->postFromTemplate('savings_withdrawal', 'SS', $branch, null, ['amount' => '200000'], 'Penarikan');
        $voluntarySavings = Account::where('code', '2.1.01')->firstOrFail();

        $ledger = $this->ledgerService->generalLedger($voluntarySavings, $branch->id, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30'));

        expect($ledger['opening_balance'])->toBe('1000000.00')
            ->and($ledger['rows']->map(fn (array $row) => [$row['detail']->debit, $row['detail']->credit, $row['balance']])->all())->toBe([
                ['0.00', '500000.00', '1500000.00'],
                ['200000.00', '0.00', '1300000.00'],
            ])
            ->and($ledger['total_debit'])->toBe('200000.00')
            ->and($ledger['total_credit'])->toBe('500000.00')
            ->and($ledger['closing_balance'])->toBe('1300000.00');
    });
});
