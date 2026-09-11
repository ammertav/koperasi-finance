<?php

namespace App\Mockups;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Carbon;

/**
 * Data contoh pinjaman per anggota aktif (M04). Sekitar 40% anggota punya pinjaman aktif dengan sebaran
 * kolektibilitas sesuai BRIEF; Cabang 05 sengaja lebih buruk. Jadwal angsuran ditambahkan di M4.
 */
class LoanMockup
{
    public const COLLECTIBILITIES = [
        'current' => 'Lancar',
        'substandard' => 'Kurang Lancar',
        'doubtful' => 'Diragukan',
        'loss' => 'Macet',
    ];

    public const STATUSES = [
        'active' => 'Aktif',
        'paid_off' => 'Lunas',
    ];

    private const TENORS = [6, 12, 18, 24, 36, 48, 60];

    /**
     * Peluang kolektibilitas dalam persen: [lancar, kurang lancar, diragukan, macet].
     *
     * @var array<string, array{0: int, 1: int, 2: int, 3: int}>
     */
    private const COLLECTIBILITY_ODDS = [
        'default' => [85, 8, 4, 3],
        '05' => [60, 17, 12, 11],
    ];

    /**
     * @var array<int, array<int, array<string, mixed>>>
     */
    private static array $loansByMember = [];

    private static ?Generator $faker = null;

    /**
     * Pinjaman aktif dan riwayat satu anggota, terbaru lebih dulu.
     *
     * @param  array<string, mixed>  $member
     * @return array<int, array<string, mixed>>
     */
    public static function forMember(array $member): array
    {
        if ($member['status'] !== 'active') {
            return [];
        }

        return self::$loansByMember[$member['id']] ??= self::generate($member);
    }

    /**
     * @param  array<string, mixed>  $member
     * @return array<int, array<string, mixed>>
     */
    public static function activeLoans(array $member): array
    {
        return array_values(array_filter(self::forMember($member), fn (array $loan) => $loan['status'] === 'active'));
    }

    /**
     * @param  array<int, array<string, mixed>>  $loans
     */
    public static function worstCollectibility(array $loans): ?string
    {
        $ranks = array_flip(array_keys(self::COLLECTIBILITIES));

        return collect($loans)->pluck('collectibility')->sortByDesc(fn (string $code) => $ranks[$code])->first();
    }

    /**
     * @param  array<string, mixed>  $member
     * @return array<int, array<string, mixed>>
     */
    private static function generate(array $member): array
    {
        $faker = self::$faker ??= FakerFactory::create('id_ID');
        $faker->seed($member['id'] * 31);

        $bookDate = Carbon::parse($member['office_book_date']);
        $monthsAsMember = max(1, (int) $member['activation_date']->diffInMonths($bookDate));
        $sequence = $member['id'] % 10000;
        $isScenario = $member['nik'] === config('demo.scenario.duplicate_member.nik');
        $hasActiveLoan = $faker->boolean(40) || $isScenario;
        $hasPaidOffLoan = $faker->boolean(30);
        $loans = [];

        if ($hasActiveLoan) {
            $collectibility = $isScenario ? 'current' : self::randomCollectibility($faker, $member['office_code']);
            $daysPastDue = match ($collectibility) {
                'current' => $faker->boolean(80) ? 0 : $faker->numberBetween(1, 30),
                'substandard' => $faker->numberBetween(31, 90),
                'doubtful' => $faker->numberBetween(91, 180),
                default => $faker->numberBetween(181, 330),
            };

            // Anggota baru belum mungkin menunggak berbulan-bulan.
            if ($monthsAsMember <= (int) ceil($daysPastDue / 30)) {
                $collectibility = 'current';
                $daysPastDue = 0;
            }

            $arrearMonths = (int) ceil($daysPastDue / 30);
            $productCode = $faker->boolean(65) ? 'PUM' : 'PUS';
            $tenor = $faker->randomElement(self::tenorsFor($productCode, $arrearMonths + 2));
            $elapsedMonths = $faker->numberBetween($arrearMonths + 1, min($tenor - 1, max($arrearMonths + 1, $monthsAsMember)));

            $loans[] = self::makeLoan($faker, $member, $productCode, $tenor, $elapsedMonths, $arrearMonths, $daysPastDue, $collectibility, 'active', $bookDate, $sequence * 10 + 2);
        }

        if ($hasPaidOffLoan && $monthsAsMember >= 20) {
            $tenor = $faker->randomElement([6, 12]);
            $elapsedMonths = $faker->numberBetween($tenor + 1, $monthsAsMember - 1);

            $loans[] = self::makeLoan($faker, $member, 'PUM', $tenor, $elapsedMonths, 0, 0, 'current', 'paid_off', $bookDate, $sequence * 10 + 1);
        }

        return $loans;
    }

    /**
     * @param  array<string, mixed>  $member
     * @return array<string, mixed>
     */
    private static function makeLoan(
        Generator $faker,
        array $member,
        string $productCode,
        int $tenor,
        int $elapsedMonths,
        int $arrearMonths,
        int $daysPastDue,
        string $collectibility,
        string $status,
        Carbon $bookDate,
        int $numberSequence,
    ): array {
        $principal = ($productCode === 'PUM' ? $faker->numberBetween(3, 40) : $faker->numberBetween(10, 90)) * 1000000;
        $disbursementDate = $bookDate->copy()->subMonths($elapsedMonths)->subDays($faker->numberBetween(0, 20));
        $maturityDate = $disbursementDate->copy()->addMonths($tenor);
        $isActive = $status === 'active';
        $paidInstallments = $isActive ? $elapsedMonths - $arrearMonths : $tenor;

        return [
            'id' => $member['id'] * 10 + $numberSequence % 10,
            'number' => sprintf('PJ-%s-%06d', $member['office_code'], $numberSequence),
            'member_id' => $member['id'],
            'office_id' => $member['office_id'],
            'product_code' => $productCode,
            'product_name' => config("demo.loan_products.{$productCode}.name"),
            'principal' => $principal,
            'tenor_months' => $tenor,
            'installment_amount' => self::installment($productCode, $principal, $tenor),
            'disbursement_date' => $disbursementDate,
            'maturity_date' => $maturityDate,
            'paid_installments' => $paidInstallments,
            'outstanding_principal' => $isActive ? (int) (ceil($principal * ($tenor - $paidInstallments) / $tenor / 1000) * 1000) : 0,
            'days_past_due' => $daysPastDue,
            'collectibility' => $collectibility,
            'collectibility_label' => self::COLLECTIBILITIES[$collectibility],
            'status' => $status,
            'status_label' => self::STATUSES[$status],
            'paid_off_date' => $isActive ? null : $maturityDate,
            'collateral' => self::collateral($faker, $principal, $isActive),
        ];
    }

    /**
     * Angsuran per bulan dibulatkan ke atas per Rp100: flat untuk PUM, anuitas untuk PUS.
     */
    private static function installment(string $productCode, int $principal, int $tenor): int
    {
        $product = config("demo.loan_products.{$productCode}");
        $monthlyRate = $product['annual_rate'] / 100 / 12;

        $amount = $product['interest_method'] === 'flat'
            ? $principal / $tenor + $principal * $monthlyRate
            : $principal * $monthlyRate / (1 - (1 + $monthlyRate) ** -$tenor);

        return (int) (ceil($amount / 100) * 100);
    }

    /**
     * @return array{type: string, description: string, estimated_value: int, status: string, status_label: string}|null
     */
    private static function collateral(Generator $faker, int $principal, bool $isActive): ?array
    {
        if ($principal < 10000000) {
            return null;
        }

        [$type, $description] = match (true) {
            $principal < 20000000 => ['BPKB Sepeda Motor', $faker->randomElement(['Honda Vario 160', 'Yamaha NMAX 155', 'Honda PCX 160']).', tahun '.$faker->numberBetween(2020, 2025)],
            $principal < 60000000 => ['BPKB Mobil', $faker->randomElement(['Toyota Avanza 1.3 G', 'Daihatsu Xenia 1.3 R', 'Suzuki Ertiga GL']).', tahun '.$faker->numberBetween(2015, 2022)],
            default => ['Sertifikat Hak Milik', 'SHM No. '.$faker->numberBetween(1000, 9999).', luas tanah '.$faker->numberBetween(90, 400).' m²'],
        };

        return [
            'type' => $type,
            'description' => $description,
            'estimated_value' => (int) (round($principal * $faker->numberBetween(130, 200) / 100 / 1000000) * 1000000),
            'status' => $isActive ? 'held' : 'released',
            'status_label' => $isActive ? 'Disimpan Koperasi' : 'Dikembalikan',
        ];
    }

    private static function randomCollectibility(Generator $faker, string $officeCode): string
    {
        $odds = self::COLLECTIBILITY_ODDS[$officeCode] ?? self::COLLECTIBILITY_ODDS['default'];
        $roll = $faker->numberBetween(1, 100);

        foreach (array_keys(self::COLLECTIBILITIES) as $index => $code) {
            $roll -= $odds[$index];

            if ($roll <= 0) {
                return $code;
            }
        }

        return 'current';
    }

    /**
     * @return array<int, int>
     */
    private static function tenorsFor(string $productCode, int $minimumTenor): array
    {
        $product = config("demo.loan_products.{$productCode}");

        return array_values(array_filter(
            self::TENORS,
            fn (int $tenor) => $tenor >= max($product['min_tenor'], $minimumTenor) && $tenor <= $product['max_tenor']
        ));
    }
}
