<?php

namespace App\Mockups;

use App\Models\Office;
use App\Models\Scopes\OfficeScope;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Sesi kas teller, perpindahan kas teller–brankas, dan posisi kas kantor (M05). Sesi dan perpindahan hari ini
 * disimpan di session demo; saldo brankas dan posisi kas dibaca dari jurnal mockup agar selalu cocok.
 */
class CashMockup
{
    /**
     * Pecahan Rupiah kertas dan logam yang beredar.
     *
     * @var array<string, array{value: int, type: string}>
     */
    public const DENOMINATIONS = [
        'paper_100000' => ['value' => 100000, 'type' => 'paper'],
        'paper_50000' => ['value' => 50000, 'type' => 'paper'],
        'paper_20000' => ['value' => 20000, 'type' => 'paper'],
        'paper_10000' => ['value' => 10000, 'type' => 'paper'],
        'paper_5000' => ['value' => 5000, 'type' => 'paper'],
        'paper_2000' => ['value' => 2000, 'type' => 'paper'],
        'paper_1000' => ['value' => 1000, 'type' => 'paper'],
        'coin_1000' => ['value' => 1000, 'type' => 'coin'],
        'coin_500' => ['value' => 500, 'type' => 'coin'],
        'coin_200' => ['value' => 200, 'type' => 'coin'],
        'coin_100' => ['value' => 100, 'type' => 'coin'],
    ];

    public const TRANSFER_DIRECTIONS = [
        'vault_to_teller' => 'Brankas ke Teller',
        'teller_to_vault' => 'Teller ke Brankas',
    ];

    public const TRANSFER_STATUSES = [
        'pending_confirmation' => 'Menunggu Konfirmasi',
        'confirmed' => 'Dikonfirmasi',
        'rejected' => 'Ditolak',
    ];

    private const SESSION_CASH_SESSIONS = 'demo.cash_sessions';

    private const SESSION_TRANSFERS = 'demo.cash_transfers';

    /**
     * Susunan pecahan contoh untuk saldo awal teller, dipakai sebagai isian awal form buka sesi.
     *
     * @return array<string, int>
     */
    public static function suggestedOpeningDenominations(): array
    {
        return [
            'paper_100000' => 100,
            'paper_50000' => 120,
            'paper_20000' => 100,
            'paper_10000' => 100,
            'paper_5000' => 100,
            'paper_2000' => 150,
            'paper_1000' => 100,
            'coin_1000' => 50,
            'coin_500' => 60,
            'coin_200' => 50,
            'coin_100' => 100,
        ];
    }

    /**
     * @param  array<string, int>  $counts
     */
    public static function denominationTotal(array $counts): int
    {
        return (int) collect(self::DENOMINATIONS)->sum(fn (array $denomination, string $key) => $denomination['value'] * (int) ($counts[$key] ?? 0));
    }

    /**
     * Sesi kas teller yang terbuka pada tanggal buku aktif.
     *
     * @return array<string, mixed>|null
     */
    public static function cashSession(User $user): ?array
    {
        return collect(self::sessionsForOffice($user->office))->firstWhere('user_id', $user->id);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function sessionsForOffice(Office $office): array
    {
        return collect(session(self::SESSION_CASH_SESSIONS, []))
            ->where('office_id', $office->id)
            ->where('book_date', $office->book_date->toDateString())
            ->map(fn (array $cashSession) => [...$cashSession, 'opened_at' => Carbon::parse($cashSession['opened_at'])])
            ->values()
            ->all();
    }

    /**
     * Buka sesi kas (KAS-01): saldo awal dihitung per pecahan dan diambil dari brankas sebagai perpindahan kas
     * yang langsung dikonfirmasi pemegang brankas.
     *
     * @param  array<string, int>  $denominations
     * @return array<string, mixed>
     */
    public static function openSession(User $user, array $denominations): array
    {
        $office = $user->office;
        $amount = self::denominationTotal($denominations);
        $transfer = self::addTransfer($user, 'vault_to_teller', $amount, 'Saldo awal sesi kas teller', true);
        $sequence = count(self::sessionsForOffice($office)) + 1;

        session()->put(self::SESSION_CASH_SESSIONS.'.'.$user->id, [
            'number' => sprintf('SK-%s-%s-%02d', $office->code, $office->book_date->format('Ymd'), $sequence),
            'office_id' => $office->id,
            'user_id' => $user->id,
            'teller_name' => $user->name,
            'book_date' => $office->book_date->toDateString(),
            'opened_at' => $transfer['created_at']->toDateTimeString(),
            'opening_balance' => $amount,
            'denominations' => $denominations,
            'transfer_number' => $transfer['number'],
            'vault_custodian_name' => $transfer['confirmed_by_name'],
            'status' => 'open',
        ]);

        return self::cashSession($user);
    }

    /**
     * Mutasi kas teller hari ini dengan saldo berjalan (KAS-11).
     *
     * @param  array<string, mixed>  $cashSession
     * @return array{rows: array<int, array<string, mixed>>, summary: array{opening_balance: int, cash_in: int, cash_out: int, balance: int}}
     */
    public static function tellerMutations(array $cashSession): array
    {
        $rows = [];

        foreach (SavingsMockup::transactions($cashSession['office_id']) as $transaction) {
            if ($transaction['status'] === 'posted' && $transaction['teller_id'] === $cashSession['user_id']) {
                $isDeposit = $transaction['type'] === 'deposit';
                $rows[] = [
                    'time' => $transaction['posted_at'],
                    'reference' => $transaction['number'],
                    'description' => "{$transaction['description']} · {$transaction['member_name']} ({$transaction['account_number']})",
                    'cash_in' => $isDeposit ? $transaction['amount'] : 0,
                    'cash_out' => $isDeposit ? 0 : $transaction['amount'],
                    'transaction_id' => $transaction['id'],
                ];
            }
        }

        foreach (self::transfers($cashSession['office_id']) as $transfer) {
            if ($transfer['status'] === 'confirmed' && $transfer['requested_by_id'] === $cashSession['user_id'] && ! $transfer['is_session_opening']) {
                $isIn = $transfer['direction'] === 'vault_to_teller';
                $rows[] = [
                    'time' => $transfer['confirmed_at'],
                    'reference' => $transfer['number'],
                    'description' => $transfer['direction_label'].($transfer['note'] ? " · {$transfer['note']}" : ''),
                    'cash_in' => $isIn ? $transfer['amount'] : 0,
                    'cash_out' => $isIn ? 0 : $transfer['amount'],
                    'transaction_id' => null,
                ];
            }
        }

        usort($rows, fn (array $a, array $b) => $a['time'] <=> $b['time']);

        $balance = $cashSession['opening_balance'];

        foreach ($rows as $index => $row) {
            $balance += $row['cash_in'] - $row['cash_out'];
            $rows[$index]['balance'] = $balance;
        }

        return [
            'rows' => $rows,
            'summary' => [
                'opening_balance' => $cashSession['opening_balance'],
                'cash_in' => array_sum(array_column($rows, 'cash_in')),
                'cash_out' => array_sum(array_column($rows, 'cash_out')),
                'balance' => $balance,
            ],
        ];
    }

    public static function tellerBalance(User $user): int
    {
        $cashSession = self::cashSession($user);

        return $cashSession ? self::tellerMutations($cashSession)['summary']['balance'] : 0;
    }

    public static function vaultBalance(Office $office): int
    {
        return AccountingMockup::balance($office->id, '1.1.02', $office->book_date);
    }

    /**
     * Perpindahan kas kantor pada tanggal buku aktif, terbaru lebih dulu. Tanpa kantor berarti semua kantor.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function transfers(?int $officeId = null): array
    {
        return collect(session(self::SESSION_TRANSFERS, []))
            ->when($officeId !== null, fn ($transfers) => $transfers->where('office_id', $officeId))
            ->map(self::hydrateTransfer(...))
            ->sortByDesc('created_at')
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findTransfer(User $user, int $id): ?array
    {
        $transfer = self::transfers()[$id] ?? null;

        if ($transfer === null || (! $user->canAccessAllOffices() && $transfer['office_id'] !== $user->office_id)) {
            return null;
        }

        return $transfer;
    }

    /**
     * Ajukan perpindahan kas (KAS-02). Pengambilan saldo awal sesi langsung dikonfirmasi kepala cabang
     * sebagai pemegang brankas; perpindahan lain menunggu konfirmasi pihak kedua.
     *
     * @return array<string, mixed>
     */
    public static function addTransfer(User $user, string $direction, int $amount, ?string $note, bool $isSessionOpening = false): array
    {
        $office = $user->office;
        $transfers = session(self::SESSION_TRANSFERS, []);
        $id = count($transfers) + 1;
        $sequence = collect($transfers)->where('office_id', $office->id)->count() + 1;
        $now = self::bookDateTime($user);

        $transfer = [
            'id' => $id,
            'number' => sprintf('MK-%s-%s-%04d', $office->code, $office->book_date->format('Ymd'), $sequence),
            'office_id' => $office->id,
            'office_code' => $office->code,
            'book_date' => $office->book_date->toDateString(),
            'direction' => $direction,
            'amount' => $amount,
            'note' => $note,
            'is_session_opening' => $isSessionOpening,
            'requested_by_id' => $user->id,
            'requested_by_name' => $user->name,
            'created_at' => $now,
            'confirmed_by_name' => $isSessionOpening ? self::branchHeadName($office) : null,
            'confirmed_at' => $isSessionOpening ? $now : null,
            'rejection_reason' => null,
            'status' => $isSessionOpening ? 'confirmed' : 'pending_confirmation',
        ];

        session()->put(self::SESSION_TRANSFERS.'.'.$id, $transfer);

        return self::hydrateTransfer($transfer);
    }

    /**
     * Pihak kedua perpindahan kas: pengguna lain di kantor yang sama dengan wewenang setujui kas (kepala cabang)
     * atau admin pembukuan cabang.
     *
     * @param  array<string, mixed>  $transfer
     */
    public static function canConfirm(array $transfer, User $user): bool
    {
        return $transfer['status'] === 'pending_confirmation'
            && $transfer['requested_by_id'] !== $user->id
            && $transfer['office_id'] === $user->office_id
            && ($user->hasAccess('cash', 'approve') || $user->hasRole('ADC'));
    }

    /**
     * @param  array<string, mixed>  $transfer
     */
    public static function confirmTransfer(array $transfer, User $user): void
    {
        self::updateTransfer($transfer, [
            'status' => 'confirmed',
            'confirmed_by_name' => $user->name,
            'confirmed_at' => self::bookDateTime($user),
        ]);
    }

    /**
     * @param  array<string, mixed>  $transfer
     */
    public static function rejectTransfer(array $transfer, User $user, string $reason): void
    {
        self::updateTransfer($transfer, [
            'status' => 'rejected',
            'confirmed_by_name' => $user->name,
            'confirmed_at' => self::bookDateTime($user),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Posisi kas teller, brankas, dan bank setiap kantor per tanggal buku (KAS-10).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function positions(User $user): array
    {
        return AccountingMockup::offices()
            ->filter(fn (Office $office) => $user->canAccessAllOffices() || $office->id === $user->office_id)
            ->map(function (Office $office) {
                $teller = AccountingMockup::balance($office->id, '1.1.01', $office->book_date);
                $vault = AccountingMockup::balance($office->id, '1.1.02', $office->book_date);
                $bank = AccountingMockup::balance($office->id, '1.1.03', $office->book_date);
                $sessions = self::sessionsForOffice($office);

                return [
                    'office_id' => $office->id,
                    'office_code' => $office->code,
                    'office_name' => $office->name,
                    'book_date' => $office->book_date,
                    'teller_cash' => $teller,
                    'vault_cash' => $vault,
                    'bank' => $bank,
                    'total' => $teller + $vault + $bank,
                    'open_session_count' => count($sessions),
                    'pending_transfer_count' => collect(self::transfers($office->id))->where('status', 'pending_confirmation')->count(),
                ];
            })
            ->values()
            ->all();
    }

    private static function branchHeadName(Office $office): string
    {
        return User::withoutGlobalScope(OfficeScope::class)
            ->where('office_id', $office->id)
            ->whereHas('roles', fn ($query) => $query->where('code', 'KCB'))
            ->value('name') ?? 'Kepala Cabang';
    }

    /**
     * @param  array<string, mixed>  $transfer
     * @param  array<string, mixed>  $changes
     */
    private static function updateTransfer(array $transfer, array $changes): void
    {
        $key = self::SESSION_TRANSFERS.'.'.$transfer['id'];

        session()->put($key, array_merge(session($key), $changes));
    }

    /**
     * @param  array<string, mixed>  $transfer
     * @return array<string, mixed>
     */
    private static function hydrateTransfer(array $transfer): array
    {
        foreach (['book_date', 'created_at', 'confirmed_at'] as $field) {
            $transfer[$field] = $transfer[$field] === null ? null : Carbon::parse($transfer[$field]);
        }

        $transfer['direction_label'] = self::TRANSFER_DIRECTIONS[$transfer['direction']];
        $transfer['status_label'] = self::TRANSFER_STATUSES[$transfer['status']];

        return $transfer;
    }

    private static function bookDateTime(User $user): string
    {
        return $user->office->book_date->copy()->setTimeFrom(now())->toDateTimeString();
    }
}
