<?php

namespace App\Mockups;

use App\Models\Account;
use App\Models\Office;
use App\Models\Scopes\OfficeScope;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Jurnal, buku besar, dan neraca saldo versi mockup (AKT-05). Saldo awal setiap kantor diturunkan dari data contoh
 * anggota, simpanan, dan pinjaman pada awal periode rinci; jurnal rinci dibentuk dari mutasi simpanan contoh dan
 * transaksi demo di session. Akun RAK cabang menjadi penyeimbang buku cabang, dan SHU Tahun Lalu penyeimbang buku
 * pusat, sehingga setiap kantor seimbang dan jumlah RAK konsolidasi nol.
 */
class AccountingMockup
{
    public const TRANSACTION_TYPES = [
        'savings_deposit' => 'Setoran Simpanan',
        'savings_withdrawal' => 'Penarikan Simpanan',
        'vault_to_teller' => 'Brankas ke Teller',
        'teller_to_vault' => 'Teller ke Brankas',
    ];

    /**
     * @var Collection<int, Office>|null
     */
    private static ?Collection $offices = null;

    /**
     * @var array<string, array<string, mixed>>|null
     */
    private static ?array $accounts = null;

    /**
     * @var array<int, array<string, int>>|null
     */
    private static ?array $openingBalances = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private static ?array $generatedJournals = null;

    /**
     * @return Collection<int, Office>
     */
    public static function offices(): Collection
    {
        return self::$offices ??= Office::withoutGlobalScope(OfficeScope::class)->orderBy('code')->get();
    }

    /**
     * Akun posting dari bagan akun nyata M1, dikunci dengan kode akun.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function accounts(): array
    {
        return self::$accounts ??= Account::where('is_postable', true)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (Account $account) => [$account->code => [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'normal_balance' => $account->normal_balance,
                'is_inter_office' => str_starts_with($account->code, '1.9.'),
            ]])
            ->all();
    }

    /**
     * Awal periode jurnal rinci; saldo sebelum tanggal ini diringkas sebagai saldo awal.
     */
    public static function detailStartDate(Office $office): Carbon
    {
        return $office->book_date->copy()->subDays(config('demo.cash.detail_days'));
    }

    /**
     * Batasi filter tanggal ke periode yang punya jurnal rinci: sejak awal periode rinci sampai tanggal buku.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function clampPeriod(Office $office, Carbon $startDate, Carbon $endDate): array
    {
        $endDate = $endDate->copy()->min($office->book_date)->max(self::detailStartDate($office));

        return [$startDate->copy()->max(self::detailStartDate($office))->min($endDate), $endDate];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function journals(): array
    {
        return [...self::generatedJournals(), ...self::sessionJournals()];
    }

    /**
     * Jurnal kantor dalam rentang tanggal, terbaru lebih dulu. Tanpa kantor berarti semua kantor.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function journalsFor(?int $officeId, Carbon $startDate, Carbon $endDate): array
    {
        return collect(self::journals())
            ->filter(fn (array $journal) => ($officeId === null || $journal['office_id'] === $officeId)
                && $journal['book_date']->betweenIncluded($startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()))
            ->sortByDesc(fn (array $journal) => $journal['book_date']->format('Ymd').$journal['number'])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findJournal(User $user, string $number): ?array
    {
        $journal = collect(self::journals())->firstWhere('number', $number);

        if ($journal === null || (! $user->canAccessAllOffices() && $journal['office_id'] !== $user->office_id)) {
            return null;
        }

        return $journal;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function journalForReference(string $reference): ?array
    {
        return collect(self::sessionJournals())->firstWhere('reference', $reference);
    }

    /**
     * Saldo akun (debit positif) sampai akhir tanggal tertentu.
     */
    public static function balance(int $officeId, string $accountCode, Carbon $date): int
    {
        $balance = self::openingBalances()[$officeId][$accountCode] ?? 0;

        foreach (self::journals() as $journal) {
            if ($journal['office_id'] !== $officeId || $journal['book_date']->gt($date)) {
                continue;
            }

            foreach ($journal['lines'] as $line) {
                if ($line['account_code'] === $accountCode) {
                    $balance += $line['debit'] - $line['credit'];
                }
            }
        }

        return $balance;
    }

    /**
     * Neraca saldo per kantor atau konsolidasi.
     *
     * @return array{rows: array<int, array<string, mixed>>, totals: array<string, int>, is_balanced: bool}
     */
    public static function trialBalance(?int $officeId, Carbon $startDate, Carbon $endDate): array
    {
        $opening = [];
        $debit = [];
        $credit = [];

        foreach (self::openingBalances() as $balanceOfficeId => $balances) {
            if ($officeId === null || $balanceOfficeId === $officeId) {
                foreach ($balances as $code => $amount) {
                    $opening[$code] = ($opening[$code] ?? 0) + $amount;
                }
            }
        }

        foreach (self::journals() as $journal) {
            if (($officeId !== null && $journal['office_id'] !== $officeId) || $journal['book_date']->gt($endDate)) {
                continue;
            }

            $isBeforePeriod = $journal['book_date']->lt($startDate->copy()->startOfDay());

            foreach ($journal['lines'] as $line) {
                $code = $line['account_code'];

                if ($isBeforePeriod) {
                    $opening[$code] = ($opening[$code] ?? 0) + $line['debit'] - $line['credit'];
                } else {
                    $debit[$code] = ($debit[$code] ?? 0) + $line['debit'];
                    $credit[$code] = ($credit[$code] ?? 0) + $line['credit'];
                }
            }
        }

        $rows = [];

        foreach (self::accounts() as $code => $account) {
            $openingBalance = $opening[$code] ?? 0;
            $closingBalance = $openingBalance + ($debit[$code] ?? 0) - ($credit[$code] ?? 0);

            if ($openingBalance === 0 && ! isset($debit[$code]) && ! isset($credit[$code])) {
                continue;
            }

            $rows[] = [
                'account' => $account,
                'opening_debit' => max($openingBalance, 0),
                'opening_credit' => max(-$openingBalance, 0),
                'debit' => $debit[$code] ?? 0,
                'credit' => $credit[$code] ?? 0,
                'closing_debit' => max($closingBalance, 0),
                'closing_credit' => max(-$closingBalance, 0),
            ];
        }

        $totals = [];

        foreach (['opening_debit', 'opening_credit', 'debit', 'credit', 'closing_debit', 'closing_credit'] as $column) {
            $totals[$column] = array_sum(array_column($rows, $column));
        }

        return [
            'rows' => $rows,
            'totals' => $totals,
            'is_balanced' => $totals['closing_debit'] === $totals['closing_credit'],
        ];
    }

    /**
     * Buku besar satu akun dengan saldo berjalan mengikuti saldo normal akun.
     *
     * @return array{opening_balance: int, rows: array<int, array<string, mixed>>, total_debit: int, total_credit: int, closing_balance: int}
     */
    public static function ledger(string $accountCode, ?int $officeId, Carbon $startDate, Carbon $endDate): array
    {
        $sign = (self::accounts()[$accountCode]['normal_balance'] ?? 'debit') === 'debit' ? 1 : -1;
        $opening = 0;

        foreach (self::openingBalances() as $balanceOfficeId => $balances) {
            if ($officeId === null || $balanceOfficeId === $officeId) {
                $opening += $balances[$accountCode] ?? 0;
            }
        }

        $entries = [];

        foreach (self::journals() as $journal) {
            if (($officeId !== null && $journal['office_id'] !== $officeId) || $journal['book_date']->gt($endDate)) {
                continue;
            }

            foreach ($journal['lines'] as $line) {
                if ($line['account_code'] !== $accountCode) {
                    continue;
                }

                if ($journal['book_date']->lt($startDate->copy()->startOfDay())) {
                    $opening += $line['debit'] - $line['credit'];
                } else {
                    $entries[] = ['journal' => $journal, 'debit' => $line['debit'], 'credit' => $line['credit']];
                }
            }
        }

        usort($entries, fn (array $a, array $b) => [$a['journal']['book_date'], $a['journal']['time']] <=> [$b['journal']['book_date'], $b['journal']['time']]);

        $balance = $opening * $sign;
        $rows = [];

        foreach ($entries as $entry) {
            $balance += ($entry['debit'] - $entry['credit']) * $sign;
            $rows[] = [...$entry, 'balance' => $balance];
        }

        return [
            'opening_balance' => $opening * $sign,
            'rows' => $rows,
            'total_debit' => array_sum(array_column($entries, 'debit')),
            'total_credit' => array_sum(array_column($entries, 'credit')),
            'closing_balance' => $balance,
        ];
    }

    /**
     * Pencocokan buku pembantu simpanan dan pinjaman dengan akun kontrol per tanggal buku (AKT-08).
     *
     * @return array<int, array{label: string, account_code: string, subledger: int, ledger: int, is_matched: bool}>
     */
    public static function subledgerReconciliation(?int $officeId, Carbon $endDate): array
    {
        $subledger = array_fill_keys(['3.1.01', '3.1.02', '2.1.01', '1.2.01', '1.2.02'], 0);

        foreach (MemberMockup::all() as $member) {
            if ($officeId !== null && $member['office_id'] !== $officeId) {
                continue;
            }

            foreach (SavingsMockup::forMember($member) as $account) {
                $subledger[$account['account_code']] += $account['balance'];
            }

            foreach (LoanMockup::activeLoans($member) as $loan) {
                $subledger[$loan['product_code'] === 'PUM' ? '1.2.01' : '1.2.02'] += $loan['outstanding_principal'];
            }
        }

        $closing = collect(self::trialBalance($officeId, $endDate, $endDate)['rows'])
            ->mapWithKeys(fn (array $row) => [$row['account']['code'] => $row['account']['normal_balance'] === 'debit'
                ? $row['closing_debit'] - $row['closing_credit']
                : $row['closing_credit'] - $row['closing_debit']]);

        return collect($subledger)
            ->map(fn (int $amount, string $code) => [
                'label' => self::accounts()[$code]['name'],
                'account_code' => $code,
                'subledger' => $amount,
                'ledger' => $closing[$code] ?? 0,
                'is_matched' => $amount === ($closing[$code] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * Saldo awal per kantor per akun (debit positif) pada awal periode rinci.
     *
     * @return array<int, array<string, int>>
     */
    private static function openingBalances(): array
    {
        if (self::$openingBalances !== null) {
            return self::$openingBalances;
        }

        $membersByOffice = collect(MemberMockup::all())->groupBy('office_id');
        $headOffice = self::offices()->firstWhere('type', 'head_office');
        $balances = [];
        $headOfficeBalances = [];

        foreach (self::offices()->where('type', 'branch') as $office) {
            $branch = self::branchOpeningBalances($office, $membersByOffice->get($office->id, collect())->all());
            $branch['1.9.00'] = -array_sum($branch);
            $balances[$office->id] = $branch;
            $headOfficeBalances['1.9.'.$office->code] = -$branch['1.9.00'];
        }

        if ($headOffice) {
            $elapsedMonths = self::detailStartDate($headOffice)->month - 1;

            $headOfficeBalances += [
                '1.1.03' => (int) config('demo.opening_balance.head_office_bank'),
                '5.2.01' => $elapsedMonths * 12000000,
                '5.2.02' => $elapsedMonths * 2000000,
                '3.2.01' => -config('demo.cash.head_office_reserve'),
            ];
            $headOfficeBalances['3.3.01'] = -array_sum($headOfficeBalances);
            $balances[$headOffice->id] = $headOfficeBalances;
        }

        return self::$openingBalances = $balances;
    }

    /**
     * @param  array<int, array<string, mixed>>  $members
     * @return array<string, int>
     */
    private static function branchOpeningBalances(Office $office, array $members): array
    {
        $start = self::detailStartDate($office);
        $yearStart = $start->copy()->startOfYear();
        $monthlyRate = fn (string $productCode) => config("demo.loan_products.{$productCode}.annual_rate") / 100 / 12;
        $balances = array_fill_keys(['1.1.02', '1.2.01', '1.2.02', '3.1.01', '3.1.02', '2.1.01', '4.1.01', '4.1.02', '4.2.01', '4.2.02', '5.1.01', '5.2.01', '5.2.02'], 0);

        foreach ($members as $member) {
            foreach (SavingsMockup::forMember($member) as $account) {
                $balances[$account['account_code']] -= SavingsMockup::balanceBefore($account, $start);
            }

            foreach (LoanMockup::activeLoans($member) as $loan) {
                $isGeneral = $loan['product_code'] === 'PUM';
                $balances[$isGeneral ? '1.2.01' : '1.2.02'] += $loan['outstanding_principal'];

                // Jasa tahun berjalan: angsuran yang sudah dibayar sejak awal tahun.
                $monthsThisYear = min($loan['paid_installments'], (int) $loan['disbursement_date']->copy()->max($yearStart)->diffInMonths($start));
                $monthlyInterest = ($isGeneral ? $loan['principal'] : $loan['outstanding_principal']) * $monthlyRate($loan['product_code']);
                $balances[$isGeneral ? '4.1.01' : '4.1.02'] -= (int) (round($monthlyInterest * max(0, $monthsThisYear) / 100) * 100);

                if ($loan['disbursement_date']->gte($yearStart) && $loan['disbursement_date']->lt($start)) {
                    $balances['4.2.01'] -= (int) round($loan['principal'] * config('demo.loan_fees.provision_rate') / 100);
                    $balances['4.2.02'] -= config('demo.loan_fees.admin_fee');
                }
            }
        }

        $faker = FakerFactory::create('id_ID');
        $faker->seed(config('demo.members.seed') * 7 + (int) $office->code);
        $elapsedMonths = $start->month - 1;

        [$minimumVault, $maximumVault] = config('demo.cash.vault_opening_millions');
        $balances['1.1.02'] = $faker->numberBetween($minimumVault, $maximumVault) * 1000000;
        $balances['5.1.01'] = (int) (round(-$balances['2.1.01'] * config('demo.savings.voluntary_annual_rate') / 100 / 12 * $elapsedMonths / 100) * 100);
        $balances['5.2.01'] = $elapsedMonths * $faker->numberBetween(30, 50) * 100000;
        $balances['5.2.02'] = $elapsedMonths * $faker->numberBetween(10, 20) * 100000;

        return $balances;
    }

    /**
     * Jurnal dari mutasi simpanan contoh selama periode rinci. Setiap hari kas teller dimulai dan diakhiri nol:
     * kekurangan diambil dari brankas di pagi hari, kelebihan disetor ke brankas sore hari.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function generatedJournals(): array
    {
        if (self::$generatedJournals !== null) {
            return self::$generatedJournals;
        }

        $membersByOffice = collect(MemberMockup::all())->groupBy('office_id');
        $tellerNames = self::tellerNames();
        $journals = [];

        foreach (self::offices()->where('type', 'branch') as $office) {
            $start = self::detailStartDate($office);
            $transactionsByDate = [];

            foreach ($membersByOffice->get($office->id, collect()) as $member) {
                foreach (SavingsMockup::forMember($member) as $account) {
                    foreach (SavingsMockup::generatedTransactionsSince($account, $start) as $transaction) {
                        $transactionsByDate[$transaction['date']->toDateString()][] = [...$transaction, 'account' => $account];
                    }
                }
            }

            ksort($transactionsByDate);
            $tellerName = $tellerNames[$office->id] ?? 'Teller';

            foreach ($transactionsByDate as $date => $transactions) {
                $bookDate = Carbon::parse($date);
                $sequence = 0;
                $net = collect($transactions)->sum(fn (array $row) => $row['type'] === 'deposit' ? $row['amount'] : -$row['amount']);
                usort($transactions, fn (array $a, array $b) => $a['account']['number'] <=> $b['account']['number']);

                if ($net < 0) {
                    $journals[] = self::cashTransferJournal($office, $bookDate->copy()->setTime(8, 0), ++$sequence, 'vault_to_teller', -$net, 'MK-'.$office->code.'-'.$bookDate->format('Ymd').'-PAGI', $tellerName);
                }

                foreach ($transactions as $index => $transaction) {
                    $journals[] = self::savingsJournal($office, $bookDate->copy()->setTime(8, 30)->addMinutes($index * 3), ++$sequence, $transaction, $transaction['account'], $tellerName);
                }

                if ($net > 0) {
                    $journals[] = self::cashTransferJournal($office, $bookDate->copy()->setTime(16, 0), ++$sequence, 'teller_to_vault', $net, 'MK-'.$office->code.'-'.$bookDate->format('Ymd').'-SORE', $tellerName);
                }
            }
        }

        return self::$generatedJournals = $journals;
    }

    /**
     * Jurnal dari transaksi demo hari ini: perpindahan kas yang dikonfirmasi dan setoran/penarikan yang diposting.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function sessionJournals(): array
    {
        $offices = self::offices()->keyBy('id');
        $items = [];

        foreach (CashMockup::transfers() as $transfer) {
            if ($transfer['status'] === 'confirmed') {
                $items[] = ['time' => $transfer['confirmed_at'], 'kind' => 'transfer', 'data' => $transfer];
            }
        }

        foreach (SavingsMockup::transactions() as $transaction) {
            if ($transaction['status'] === 'posted') {
                $items[] = ['time' => $transaction['posted_at'], 'kind' => 'savings', 'data' => $transaction];
            }
        }

        usort($items, fn (array $a, array $b) => $a['time'] <=> $b['time']);

        $sequences = [];
        $journals = [];

        foreach ($items as $item) {
            $data = $item['data'];
            $office = $offices[$data['office_id']];
            $sequence = $sequences[$office->id] = ($sequences[$office->id] ?? 0) + 1;

            $journals[] = $item['kind'] === 'transfer'
                ? self::cashTransferJournal($office, $item['time'], $sequence, $data['direction'], $data['amount'], $data['number'], $data['requested_by_name'])
                : self::savingsJournal($office, $item['time'], $sequence, $data, ['number' => $data['account_number'], 'product_code' => $data['product_code'], 'member_name' => $data['member_name']], $data['teller_name']);
        }

        return $journals;
    }

    /**
     * @param  array<string, mixed>  $transaction
     * @param  array<string, mixed>  $account
     * @return array<string, mixed>
     */
    private static function savingsJournal(Office $office, Carbon $time, int $sequence, array $transaction, array $account, string $createdBy): array
    {
        $accountCode = SavingsMockup::ACCOUNT_CODES[$account['product_code']];
        $productName = SavingsMockup::PRODUCTS[$account['product_code']];
        $isDeposit = $transaction['type'] === 'deposit';
        $amount = $transaction['amount'];
        $memo = "{$account['member_name']} · {$account['number']}";

        return self::journal(
            $office,
            $time,
            $sequence,
            $isDeposit ? 'savings_deposit' : 'savings_withdrawal',
            ($isDeposit ? 'Setoran ' : 'Penarikan ').$productName.' '.$memo,
            $transaction['number'],
            $createdBy,
            $isDeposit
                ? [['1.1.01', $amount, 0, 'Kas diterima teller'], [$accountCode, 0, $amount, $memo]]
                : [[$accountCode, $amount, 0, $memo], ['1.1.01', 0, $amount, 'Kas dibayarkan teller']],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function cashTransferJournal(Office $office, Carbon $time, int $sequence, string $direction, int $amount, string $reference, string $createdBy): array
    {
        $isToTeller = $direction === 'vault_to_teller';

        return self::journal(
            $office,
            $time,
            $sequence,
            $direction,
            $isToTeller ? 'Pengambilan kas brankas oleh teller' : 'Penyetoran kas teller ke brankas',
            $reference,
            $createdBy,
            $isToTeller
                ? [['1.1.01', $amount, 0, null], ['1.1.02', 0, $amount, null]]
                : [['1.1.02', $amount, 0, null], ['1.1.01', 0, $amount, null]],
        );
    }

    /**
     * @param  array<int, array{0: string, 1: int, 2: int, 3: string|null}>  $lines
     * @return array<string, mixed>
     */
    private static function journal(Office $office, Carbon $time, int $sequence, string $transactionType, string $description, string $reference, string $createdBy, array $lines): array
    {
        $accounts = self::accounts();

        return [
            'number' => sprintf('JU-%s-%s-%04d', $office->code, $time->format('Ymd'), $sequence),
            'office_id' => $office->id,
            'office_code' => $office->code,
            'office_name' => $office->name,
            'book_date' => $time->copy()->startOfDay(),
            'time' => $time,
            'transaction_type' => $transactionType,
            'transaction_type_label' => self::TRANSACTION_TYPES[$transactionType],
            'description' => $description,
            'reference' => $reference,
            'created_by_name' => $createdBy,
            'status' => 'posted',
            'total_amount' => array_sum(array_column($lines, 1)),
            'lines' => array_map(fn (array $line) => [
                'account_code' => $line[0],
                'account_name' => $accounts[$line[0]]['name'] ?? $line[0],
                'debit' => $line[1],
                'credit' => $line[2],
                'description' => $line[3],
            ], $lines),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function tellerNames(): array
    {
        return User::withoutGlobalScope(OfficeScope::class)
            ->whereHas('roles', fn ($query) => $query->where('code', 'TLR'))
            ->pluck('name', 'office_id')
            ->all();
    }
}
