<?php

namespace App\Mockups;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Carbon;

/**
 * Data contoh rekening simpanan per anggota aktif (M03). Pokok dan wajib terbuka saat aktivasi (SIM-02),
 * sukarela dimiliki sebagian anggota. Mutasi rekening ditambahkan di M3.
 */
class SavingsMockup
{
    public const PRODUCTS = [
        'SP' => 'Simpanan Pokok',
        'SW' => 'Simpanan Wajib',
        'SS' => 'Simpanan Sukarela',
    ];

    /**
     * @var array<int, array<int, array<string, mixed>>>
     */
    private static array $accountsByMember = [];

    private static ?Generator $faker = null;

    /**
     * @param  array<string, mixed>  $member
     * @return array<int, array<string, mixed>>
     */
    public static function forMember(array $member): array
    {
        if ($member['status'] !== 'active') {
            return [];
        }

        return self::$accountsByMember[$member['id']] ??= self::generate($member);
    }

    /**
     * @param  array<string, mixed>  $member
     * @return array<int, array<string, mixed>>
     */
    private static function generate(array $member): array
    {
        $faker = self::$faker ??= FakerFactory::create('id_ID');
        $faker->seed($member['id'] * 17);

        $bookDate = Carbon::parse($member['office_book_date']);
        $activationDate = $member['activation_date'];
        $sequence = $member['id'] % 10000;
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
                $bookDate->copy()->startOfMonth()->subMonths($monthsAsMember - $paidMandatoryMonths)->addDays($faker->numberBetween(0, 9))->min($bookDate)
            ),
        ];

        if ($faker->boolean(70)) {
            $accounts[] = self::account(
                $member,
                'SS',
                $sequence,
                $activationDate->copy()->addDays($faker->numberBetween(0, 30))->min($bookDate),
                $faker->numberBetween(1, 400) * 25000,
                $bookDate->copy()->subDays($faker->numberBetween(0, 45))
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
            'product_code' => $productCode,
            'product_name' => self::PRODUCTS[$productCode],
            'opened_date' => $openedDate,
            'last_transaction_date' => $lastTransactionDate->max($openedDate),
            'balance' => $balance,
            'status' => 'active',
        ];
    }
}
