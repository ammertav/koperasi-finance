<?php

namespace App\Mockups;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Carbon;

/**
 * Data contoh rekening simpanan per anggota aktif (M03). Pokok dan wajib terbuka saat aktivasi (SIM-02),
 * sukarela dimiliki sebagian anggota. Mutasi 6 bulan dibangun mundur dari saldo agar saldo akhir selalu cocok,
 * lalu ditambah transaksi teller demo yang disimpan di session.
 */
class SavingsMockup
{
    public const PRODUCTS = [
        'SP' => 'Simpanan Pokok',
        'SW' => 'Simpanan Wajib',
        'SS' => 'Simpanan Sukarela',
    ];

    public const ACCOUNT_CODES = [
        'SP' => '3.1.01',
        'SW' => '3.1.02',
        'SS' => '2.1.01',
    ];

    public const TRANSACTION_TYPES = [
        'opening_balance' => 'Saldo Awal',
        'deposit' => 'Setoran',
        'withdrawal' => 'Penarikan',
    ];

    public const TRANSACTION_STATUSES = [
        'posted' => 'Diposting',
        'pending_authorization' => 'Menunggu Otorisasi',
        'rejected' => 'Ditolak',
    ];

    private const SESSION_TRANSACTIONS = 'demo.savings_transactions';

    private const TRANSACTION_DATE_FIELDS = ['book_date', 'created_at', 'authorized_at', 'posted_at'];

    /**
     * @var array<int, array<int, array<string, mixed>>>
     */
    private static array $accountsByMember = [];

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private static array $generatedMutations = [];

    private static ?Generator $faker = null;

    /**
     * Konfigurasi produk simpanan untuk halaman lihat produk (SIM-01).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function products(): array
    {
        $savings = config('demo.savings');
        $rupiah = fn (int $amount) => 'Rp '.number_format($amount, 0, ',', '.');

        return [
            'SP' => [
                'code' => 'SP',
                'name' => self::PRODUCTS['SP'],
                'type_label' => 'Pokok',
                'classification' => 'Ekuitas',
                'account_code' => self::ACCOUNT_CODES['SP'],
                'deposit_rule' => $rupiah($savings['principal']).' sekali saat menjadi anggota',
                'minimum_balance' => $rupiah($savings['principal']),
                'admin_fee' => 'Tidak ada',
                'interest' => 'Tidak ada jasa, ikut pembagian SHU',
                'withdrawal_rule' => 'Tidak dapat ditarik selama keanggotaan aktif',
                'opening_rule' => 'Dibuka otomatis saat aktivasi anggota',
                'is_withdrawable' => false,
            ],
            'SW' => [
                'code' => 'SW',
                'name' => self::PRODUCTS['SW'],
                'type_label' => 'Wajib',
                'classification' => 'Ekuitas',
                'account_code' => self::ACCOUNT_CODES['SW'],
                'deposit_rule' => $rupiah($savings['mandatory_monthly']).' per bulan (kelipatan)',
                'minimum_balance' => 'Tidak ada',
                'admin_fee' => 'Tidak ada',
                'interest' => 'Tidak ada jasa, ikut pembagian SHU',
                'withdrawal_rule' => 'Tidak dapat ditarik selama keanggotaan aktif',
                'opening_rule' => 'Dibuka otomatis saat aktivasi anggota',
                'is_withdrawable' => false,
            ],
            'SS' => [
                'code' => 'SS',
                'name' => self::PRODUCTS['SS'],
                'type_label' => 'Sukarela',
                'classification' => 'Kewajiban',
                'account_code' => self::ACCOUNT_CODES['SS'],
                'deposit_rule' => 'Minimal '.$rupiah($savings['voluntary_minimum_deposit']).' per setoran',
                'minimum_balance' => $rupiah($savings['voluntary_minimum_balance']),
                'admin_fee' => 'Tidak ada',
                'interest' => $savings['voluntary_annual_rate'].'% per tahun dari saldo harian',
                'withdrawal_rule' => 'Dapat ditarik tunai; di atas '.$rupiah($savings['teller_withdrawal_limit']).' perlu otorisasi',
                'opening_rule' => 'Dibuka atas permintaan anggota',
                'is_withdrawable' => true,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $member
     * @return array<int, array<string, mixed>>
     */
    public static function forMember(array $member): array
    {
        if ($member['status'] !== 'active' || $member['number'] === null) {
            return [];
        }

        $accounts = self::$accountsByMember[$member['id']] ??= self::generate($member);
        $postedTransactions = collect(self::transactions())->where('status', 'posted');

        return array_map(function (array $account) use ($member, $postedTransactions) {
            $transactions = $postedTransactions->where('account_number', $account['number']);

            $account['balance'] = $account['base_balance']
                + $transactions->where('type', 'deposit')->sum('amount')
                - $transactions->where('type', 'withdrawal')->sum('amount');

            if ($transactions->isNotEmpty()) {
                $account['last_transaction_date'] = $transactions->max('book_date');
            }

            return [
                ...$account,
                'member_name' => $member['name'],
                'member_number' => $member['number'],
                'office_code' => $member['office_code'],
                'office_name' => $member['office_name'],
            ];
        }, $accounts);
    }

    /**
     * Rekening yang boleh dilihat pengguna, mengikuti pembatasan kantor anggota.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function accountsForUser(User $user, ?string $productCode = null): array
    {
        $accounts = [];

        foreach (MemberMockup::forUser($user) as $member) {
            foreach (self::forMember($member) as $account) {
                if ($productCode === null || $account['product_code'] === $productCode) {
                    $accounts[] = $account;
                }
            }
        }

        return $accounts;
    }

    /**
     * Cari rekening dari nomornya: `{kode kantor}.{produk}.{urut}` dengan urut sama dengan nomor anggota.
     *
     * @return array<string, mixed>|null
     */
    public static function findAccount(User $user, string $number): ?array
    {
        if (! preg_match('/^(\d{2})\.(SP|SW|SS)\.(\d{6})$/', $number, $parts)) {
            return null;
        }

        $member = collect(MemberMockup::forUser($user))->firstWhere('number', "{$parts[1]}.{$parts[3]}");

        return $member ? collect(self::forMember($member))->firstWhere('number', $number) : null;
    }

    /**
     * Rekening anggota yang cocok dengan nama, nomor anggota, atau nomor rekening (maksimal 10 anggota).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function search(User $user, string $keyword): array
    {
        $keyword = mb_strtolower(trim($keyword));
        $memberNumber = preg_replace('/\.(sp|sw|ss)\./', '.', $keyword);

        return collect(MemberMockup::forUser($user))
            ->filter(fn (array $member) => $member['status'] === 'active' && (
                str_contains(mb_strtolower($member['name']), $keyword)
                || str_contains((string) $member['number'], $memberNumber)
            ))
            ->take(10)
            ->flatMap(fn (array $member) => self::forMember($member))
            ->values()
            ->all();
    }

    /**
     * Mutasi rekening dengan saldo berjalan (SIM-15): histori contoh ditambah transaksi demo yang diposting.
     *
     * @param  array<string, mixed>  $account
     * @return array<int, array<string, mixed>>
     */
    public static function mutations(array $account): array
    {
        $rows = self::generatedMutations($account);

        $demoTransactions = collect(self::transactions())
            ->where('status', 'posted')
            ->where('account_number', $account['number'])
            ->sortBy('posted_at');

        foreach ($demoTransactions as $transaction) {
            $rows[] = [
                'date' => $transaction['book_date'],
                'number' => $transaction['number'],
                'type' => $transaction['type'],
                'description' => $transaction['description'],
                'amount' => $transaction['amount'],
                'transaction_id' => $transaction['id'],
            ];
        }

        $balance = 0;

        return array_map(function (array $row) use (&$balance) {
            $balance += $row['type'] === 'withdrawal' ? -$row['amount'] : $row['amount'];

            return [
                ...$row,
                'type_label' => self::TRANSACTION_TYPES[$row['type']],
                'debit' => $row['type'] === 'withdrawal' ? $row['amount'] : 0,
                'credit' => $row['type'] === 'deposit' ? $row['amount'] : 0,
                'balance' => $balance,
                'transaction_id' => $row['transaction_id'] ?? null,
            ];
        }, $rows);
    }

    /**
     * Saldo rekening dari data contoh pada awal tanggal tertentu, sebelum transaksi hari itu.
     *
     * @param  array<string, mixed>  $account
     */
    public static function balanceBefore(array $account, Carbon $date): int
    {
        return (int) collect(self::generatedMutations($account))
            ->filter(fn (array $row) => $row['type'] === 'opening_balance' || $row['date']->lt($date))
            ->sum(fn (array $row) => $row['type'] === 'withdrawal' ? -$row['amount'] : $row['amount']);
    }

    /**
     * Setoran dan penarikan data contoh sejak tanggal tertentu, dipakai untuk membentuk jurnal mockup.
     *
     * @param  array<string, mixed>  $account
     * @return array<int, array<string, mixed>>
     */
    public static function generatedTransactionsSince(array $account, Carbon $date): array
    {
        return array_values(array_filter(
            self::generatedMutations($account),
            fn (array $row) => $row['type'] !== 'opening_balance' && $row['date']->gte($date)
        ));
    }

    /**
     * Cek aturan setor dan tarik (SIM-03, SIM-05, SIM-06). Penarikan di atas batas teller menunggu otorisasi
     * kepala cabang, dan naik ke manajer pusat bila melebihi batas kepala cabang di matriks wewenang.
     *
     * @param  array<string, mixed>  $account
     * @return array{allowed: bool, message: string|null, requires_authorization: bool, authorization_role: string|null}
     */
    public static function transactionCheck(array $account, string $type, int $amount): array
    {
        $savings = config('demo.savings');
        $rupiah = fn (int $value) => 'Rp '.number_format($value, 0, ',', '.');
        $deny = fn (string $message) => ['allowed' => false, 'message' => $message, 'requires_authorization' => false, 'authorization_role' => null];

        if ($type === 'deposit') {
            return match (true) {
                $account['product_code'] === 'SP' => $deny('Simpanan pokok hanya disetor sekali saat aktivasi anggota.'),
                $account['product_code'] === 'SW' && $amount % $savings['mandatory_monthly'] !== 0 => $deny('Setoran simpanan wajib harus kelipatan '.$rupiah($savings['mandatory_monthly']).'.'),
                $account['product_code'] === 'SS' && $amount < $savings['voluntary_minimum_deposit'] => $deny('Setoran simpanan sukarela minimal '.$rupiah($savings['voluntary_minimum_deposit']).'.'),
                default => ['allowed' => true, 'message' => null, 'requires_authorization' => false, 'authorization_role' => null],
            };
        }

        if ($account['product_code'] !== 'SS') {
            return $deny("{$account['product_name']} tidak dapat ditarik selama keanggotaan aktif.");
        }

        $available = self::availableBalance($account);

        if ($amount > $available) {
            return $deny('Saldo yang dapat ditarik hanya '.$rupiah(max(0, $available)).' (saldo minimal '.$rupiah($savings['voluntary_minimum_balance']).' dan penarikan yang menunggu otorisasi tidak dapat ditarik).');
        }

        $requiresAuthorization = $amount > $savings['teller_withdrawal_limit'];
        $branchHeadLimit = (int) config('demo.authority_limits.KCB.withdrawal');

        return [
            'allowed' => true,
            'message' => null,
            'requires_authorization' => $requiresAuthorization,
            'authorization_role' => $requiresAuthorization ? ($amount <= $branchHeadLimit ? 'KCB' : 'MGR') : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $account
     */
    public static function availableBalance(array $account): int
    {
        $pendingWithdrawals = collect(self::transactions())
            ->where('status', 'pending_authorization')
            ->where('account_number', $account['number'])
            ->sum('amount');

        return $account['balance'] - config('demo.savings.voluntary_minimum_balance') - $pendingWithdrawals;
    }

    /**
     * Transaksi teller demo dari session, terbaru lebih dulu.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function transactions(?int $officeId = null): array
    {
        return collect(session(self::SESSION_TRANSACTIONS, []))
            ->when($officeId !== null, fn ($transactions) => $transactions->where('office_id', $officeId))
            ->map(self::hydrateTransaction(...))
            ->sortByDesc('created_at')
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findTransaction(User $user, int $id): ?array
    {
        $transaction = self::transactions()[$id] ?? null;

        if ($transaction === null || (! $user->canAccessAllOffices() && $transaction['office_id'] !== $user->office_id)) {
            return null;
        }

        return $transaction;
    }

    /**
     * @param  array<string, mixed>  $account
     * @return array<string, mixed>
     */
    public static function addTransaction(array $account, string $type, int $amount, User $user, ?string $authorizationRole, bool $isActivation = false): array
    {
        $office = $user->office;
        $transactions = session(self::SESSION_TRANSACTIONS, []);
        $id = count($transactions) + 1;
        $sequence = collect($transactions)->where('office_id', $office->id)->count() + 1;
        $now = self::bookDateTime($user);
        $isPosted = $authorizationRole === null;

        $transaction = [
            'id' => $id,
            'number' => sprintf('ST-%s-%s-%04d', $office->code, $office->book_date->format('Ymd'), $sequence),
            'office_id' => $office->id,
            'office_code' => $office->code,
            'office_name' => $office->name,
            'book_date' => $office->book_date->toDateString(),
            'account_number' => $account['number'],
            'product_code' => $account['product_code'],
            'member_id' => $account['member_id'],
            'member_name' => $account['member_name'],
            'member_number' => $account['member_number'],
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $account['balance'],
            'is_activation' => $isActivation,
            'teller_id' => $user->id,
            'teller_name' => $user->name,
            'created_at' => $now,
            'authorization_role' => $authorizationRole,
            'authorized_by_name' => null,
            'authorized_at' => null,
            'rejected_by_name' => null,
            'rejection_reason' => null,
            'posted_at' => $isPosted ? $now : null,
            'status' => $isPosted ? 'posted' : 'pending_authorization',
        ];

        session()->put(self::SESSION_TRANSACTIONS.'.'.$id, $transaction);

        return self::hydrateTransaction($transaction);
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    public static function authorize(array $transaction, User $user): void
    {
        $now = self::bookDateTime($user);

        session()->put(self::SESSION_TRANSACTIONS.'.'.$transaction['id'], array_merge(session(self::SESSION_TRANSACTIONS.'.'.$transaction['id']), [
            'status' => 'posted',
            'authorized_by_name' => $user->name,
            'authorized_at' => $now,
            'posted_at' => $now,
        ]));
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    public static function reject(array $transaction, User $user, string $reason): void
    {
        session()->put(self::SESSION_TRANSACTIONS.'.'.$transaction['id'], array_merge(session(self::SESSION_TRANSACTIONS.'.'.$transaction['id']), [
            'status' => 'rejected',
            'rejected_by_name' => $user->name,
            'rejection_reason' => $reason,
            'authorized_at' => self::bookDateTime($user),
        ]));
    }

    /**
     * @param  array<string, mixed>  $member
     * @return array<int, array<string, mixed>>
     */
    private static function generate(array $member): array
    {
        $bookDate = Carbon::parse($member['office_book_date']);
        $activationDate = $member['activation_date'];
        $sequence = (int) substr($member['number'], 3);

        // Anggota yang diaktifkan saat demo: rekening baru bersaldo nol, setoran pokok masuk lewat transaksi session.
        if ($member['activated_in_demo']) {
            return [
                self::account($member, 'SP', $sequence, $activationDate, 0, $activationDate),
                self::account($member, 'SW', $sequence, $activationDate, 0, $activationDate),
            ];
        }

        $faker = self::$faker ??= FakerFactory::create('id_ID');
        $faker->seed($member['id'] * 17);

        $yesterday = $bookDate->copy()->subDay();
        $monthsAsMember = (int) $activationDate->diffInMonths($bookDate) + 1;
        $paidMandatoryMonths = max(1, $monthsAsMember - ($faker->boolean(90) ? 0 : $faker->numberBetween(1, 3)));

        $accounts = [
            self::account($member, 'SP', $sequence, $activationDate, config('demo.savings.principal'), $activationDate),
            self::account(
                $member,
                'SW',
                $sequence,
                $activationDate,
                config('demo.savings.mandatory_monthly') * $paidMandatoryMonths,
                $bookDate->copy()->startOfMonth()->subMonths($monthsAsMember - $paidMandatoryMonths)->addDays($faker->numberBetween(0, 9))->min($yesterday)
            ),
        ];

        if ($faker->boolean(70)) {
            $accounts[] = self::account(
                $member,
                'SS',
                $sequence,
                $activationDate->copy()->addDays($faker->numberBetween(0, 30))->min($yesterday),
                $faker->numberBetween(1, 400) * 25000,
                $bookDate->copy()->subDays($faker->numberBetween(1, 45))
            );
        }

        return $accounts;
    }

    /**
     * @param  array<string, mixed>  $member
     * @return array<string, mixed>
     */
    private static function account(array $member, string $productCode, int $sequence, Carbon $openedDate, int $balance, Carbon $lastTransactionDate): array
    {
        return [
            'number' => sprintf('%s.%s.%06d', $member['office_code'], $productCode, $sequence),
            'member_id' => $member['id'],
            'office_id' => $member['office_id'],
            'office_book_date' => $member['office_book_date'],
            'product_code' => $productCode,
            'product_name' => self::PRODUCTS[$productCode],
            'account_code' => self::ACCOUNT_CODES[$productCode],
            'opened_date' => $openedDate,
            'last_transaction_date' => $lastTransactionDate->copy()->max($openedDate),
            'base_balance' => $balance,
            'balance' => $balance,
            'status' => 'active',
        ];
    }

    /**
     * Histori data contoh selama `history_months` bulan, dibangun mundur dari saldo contoh sehingga saldo akhir
     * selalu sama dengan saldo rekening. Tidak ada transaksi contoh pada tanggal buku aktif.
     *
     * @param  array<string, mixed>  $account
     * @return array<int, array<string, mixed>>
     */
    private static function generatedMutations(array $account): array
    {
        return self::$generatedMutations[$account['number']] ??= self::buildMutations($account);
    }

    /**
     * @param  array<string, mixed>  $account
     * @return array<int, array<string, mixed>>
     */
    private static function buildMutations(array $account): array
    {
        $bookDate = Carbon::parse($account['office_book_date']);
        $windowStart = $bookDate->copy()->startOfMonth()->subMonthsNoOverflow(config('demo.savings.history_months') - 1);
        $opened = $account['opened_date'];
        $opensInWindow = $opened->gte($windowStart);
        $balance = $account['base_balance'];

        if ($balance === 0) {
            return [self::mutation($account, $windowStart, 'opening_balance', 0, 'Saldo awal periode')];
        }

        $events = match ($account['product_code']) {
            'SP' => [],
            'SW' => self::mandatoryEvents($account, $windowStart, $balance),
            default => self::voluntaryEvents($account, $windowStart, $balance),
        };

        if ($balance > 0 && $opensInWindow) {
            $events[] = self::mutation($account, $opened, 'deposit', $balance, match ($account['product_code']) {
                'SP' => 'Setoran simpanan pokok',
                'SW' => 'Setoran simpanan wajib pertama',
                default => 'Setoran awal pembukaan rekening',
            });
            $balance = 0;
        }

        usort($events, fn (array $a, array $b) => $a['date'] <=> $b['date']);

        return [self::mutation($account, $windowStart, 'opening_balance', $balance, 'Saldo awal periode'), ...$events];
    }

    /**
     * Setoran wajib bulanan mundur dari transaksi terakhir. Saldo yang tersisa dikembalikan lewat referensi.
     *
     * @param  array<string, mixed>  $account
     * @return array<int, array<string, mixed>>
     */
    private static function mandatoryEvents(array $account, Carbon $windowStart, int &$balance): array
    {
        $monthly = config('demo.savings.mandatory_monthly');
        $opened = $account['opened_date'];
        $opensInWindow = $opened->gte($windowStart);
        $date = $account['last_transaction_date']->copy();
        $events = [];

        // Bila rekening dibuka di dalam periode, sisa saldo menjadi setoran pertama pada tanggal pembukaan.
        while ($date->gte($windowStart) && ($opensInWindow ? $balance > $monthly && $date->gt($opened) : $balance > 0)) {
            $events[] = self::mutation($account, $date, 'deposit', $monthly, 'Setoran simpanan wajib '.$date->translatedFormat('F Y'));
            $balance -= $monthly;
            $date = $date->copy()->subMonthNoOverflow();
        }

        return $events;
    }

    /**
     * Setoran dan penarikan sukarela acak mundur dari saldo akhir, tanpa pernah di bawah saldo minimal.
     *
     * @param  array<string, mixed>  $account
     * @return array<int, array<string, mixed>>
     */
    private static function voluntaryEvents(array $account, Carbon $windowStart, int &$balance): array
    {
        $faker = self::$faker ??= FakerFactory::create('id_ID');
        $faker->seed(crc32($account['number']));

        $minimumBalance = config('demo.savings.voluntary_minimum_balance');
        $opensInWindow = $account['opened_date']->gte($windowStart);
        $start = $account['opened_date']->copy()->max($windowStart);
        $span = (int) $start->diffInDays($account['last_transaction_date']);

        if ($span === 0) {
            return [];
        }

        $pool = range($opensInWindow ? 1 : 0, $span - 1);
        $offsets = $pool === [] ? [] : $faker->randomElements($pool, min($faker->numberBetween(2, 9), count($pool)));
        $offsets[] = $span;
        rsort($offsets);

        $events = [];

        foreach ($offsets as $offset) {
            $date = $start->copy()->addDays($offset);
            $depositRoom = intdiv($balance - $minimumBalance, 25000);

            if ($depositRoom >= 1 && $faker->boolean(60)) {
                $amount = $faker->numberBetween(1, min(40, $depositRoom)) * 25000;
                $balance -= $amount;
                $events[] = self::mutation($account, $date, 'deposit', $amount, 'Setoran tunai');
            } else {
                $amount = $faker->numberBetween(1, 20) * 25000;
                $balance += $amount;
                $events[] = self::mutation($account, $date, 'withdrawal', $amount, 'Penarikan tunai');
            }
        }

        return $events;
    }

    /**
     * @param  array<string, mixed>  $account
     * @return array<string, mixed>
     */
    private static function mutation(array $account, Carbon $date, string $type, int $amount, string $description): array
    {
        return [
            'date' => $date->copy()->startOfDay(),
            'number' => $type === 'opening_balance' ? null : sprintf('ST-%s-%s-%s', substr($account['number'], 0, 2), $date->format('Ymd'), str_replace('.', '', substr($account['number'], 3))),
            'type' => $type,
            'description' => $description,
            'amount' => $amount,
        ];
    }

    /**
     * @param  array<string, mixed>  $transaction
     * @return array<string, mixed>
     */
    private static function hydrateTransaction(array $transaction): array
    {
        foreach (self::TRANSACTION_DATE_FIELDS as $field) {
            $transaction[$field] = $transaction[$field] === null ? null : Carbon::parse($transaction[$field]);
        }

        $productName = self::PRODUCTS[$transaction['product_code']];

        $transaction['product_name'] = $productName;
        $transaction['type_label'] = self::TRANSACTION_TYPES[$transaction['type']];
        $transaction['status_label'] = self::TRANSACTION_STATUSES[$transaction['status']];
        $transaction['description'] = $transaction['is_activation']
            ? 'Setoran simpanan pokok aktivasi anggota'
            : ($transaction['type'] === 'deposit' ? "Setoran tunai {$productName}" : "Penarikan tunai {$productName}");
        $transaction['balance_after'] = $transaction['balance_before'] + ($transaction['type'] === 'deposit' ? $transaction['amount'] : -$transaction['amount']);

        return $transaction;
    }

    private static function bookDateTime(User $user): string
    {
        return $user->office->book_date->copy()->setTimeFrom(now())->toDateTimeString();
    }
}
