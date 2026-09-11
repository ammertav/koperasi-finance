<?php

namespace App\Mockups;

use App\Models\Office;
use App\Models\Scopes\OfficeScope;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Carbon;

/**
 * Data contoh anggota untuk prototype tampilan (M02). Dibuat deterministik per cabang dengan Faker seed tetap,
 * lalu ditimpa calon anggota baru dan keputusan persetujuan yang disimpan di session demo.
 */
class MemberMockup
{
    public const STATUSES = [
        'pending_approval' => 'Menunggu Persetujuan',
        'approved' => 'Menunggu Setoran Pokok',
        'rejected' => 'Ditolak',
        'active' => 'Aktif',
        'exited' => 'Keluar',
    ];

    public const GENDERS = [
        'male' => 'Laki-laki',
        'female' => 'Perempuan',
    ];

    public const OCCUPATIONS = [
        'Wiraswasta',
        'Pedagang',
        'Petani',
        'Karyawan Swasta',
        'Pegawai Negeri Sipil',
        'Guru',
        'Buruh Pabrik',
        'Ibu Rumah Tangga',
        'Pengemudi',
        'Peternak',
    ];

    public const HEIR_RELATIONSHIPS = [
        'Suami',
        'Istri',
        'Anak',
        'Orang Tua',
        'Saudara Kandung',
    ];

    private const SESSION_CANDIDATES = 'demo.member_candidates';

    private const SESSION_DECISIONS = 'demo.member_decisions';

    private const DATE_FIELDS = ['birth_date', 'registration_date', 'data_consent_at', 'approved_at', 'rejected_at', 'activation_date'];

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private static ?array $generated = null;

    /**
     * Semua anggota di seluruh kantor, termasuk calon anggota dan keputusan dari session.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $members = self::generated();

        foreach (session(self::SESSION_CANDIDATES, []) as $candidate) {
            $members[$candidate['id']] = $candidate;
        }

        foreach (session(self::SESSION_DECISIONS, []) as $id => $decision) {
            if (isset($members[$id])) {
                $members[$id] = array_merge($members[$id], $decision);
            }
        }

        uasort($members, fn (array $a, array $b) => [$b['registration_date'], $b['id']] <=> [$a['registration_date'], $a['id']]);

        return array_map(self::hydrate(...), $members);
    }

    /**
     * Anggota yang boleh dilihat pengguna: peran cabang hanya kantornya sendiri (ATR-01).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function forUser(User $user): array
    {
        $members = self::all();

        if ($user->canAccessAllOffices()) {
            return $members;
        }

        return array_filter($members, fn (array $member) => $member['office_id'] === $user->office_id);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(User $user, int $id): ?array
    {
        return self::forUser($user)[$id] ?? null;
    }

    /**
     * Cek NIK di seluruh kantor (AGT-02). Hasil hanya memuat identitas, status keanggotaan, dan status pinjaman
     * tanpa saldo, sesuai pengecualian akses lintas cabang PRD 5.2.
     *
     * @return array{allowed: bool, message: string|null, match: array<string, mixed>|null}
     */
    public static function nikCheck(string $nik): array
    {
        $member = collect(self::all())->first(
            fn (array $member) => $member['nik'] === $nik && $member['status'] !== 'rejected'
        );

        if ($member === null) {
            return ['allowed' => true, 'message' => null, 'match' => null];
        }

        $activeLoans = LoanMockup::activeLoans($member);
        $worstCollectibility = LoanMockup::worstCollectibility($activeLoans);

        $match = [
            'name' => $member['name'],
            'nik_masked' => $member['nik_masked'],
            'office' => "{$member['office_name']} ({$member['office_code']})",
            'status_label' => $member['status_label'],
            'active_loan_count' => count($activeLoans),
            'collectibility_label' => $worstCollectibility ? LoanMockup::COLLECTIBILITIES[$worstCollectibility] : null,
        ];

        $loanText = $activeLoans === []
            ? 'tidak memiliki pinjaman berjalan'
            : "memiliki {$match['active_loan_count']} pinjaman berjalan ({$match['collectibility_label']})";

        return [
            'allowed' => false,
            'message' => "NIK sudah terdaftar atas nama {$member['name']} di {$match['office']}, berstatus "
                ."{$member['status_label']} dan {$loanText}. Pendaftaran tidak dapat dilanjutkan.",
            'match' => $match,
        ];
    }

    /**
     * Simpan calon anggota baru di session demo dengan status menunggu persetujuan kepala cabang.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function addCandidate(array $data, User $user): array
    {
        $office = $user->office;
        $candidates = session(self::SESSION_CANDIDATES, []);

        $candidate = [
            'id' => 900000 + count($candidates) + 1,
            'office_id' => $office->id,
            'office_code' => $office->code,
            'office_name' => $office->name,
            'office_book_date' => $office->book_date->toDateString(),
            'number' => null,
            'nik' => $data['nik'],
            'name' => $data['name'],
            'gender' => $data['gender'],
            'birth_place' => $data['birth_place'],
            'birth_date' => $data['birth_date'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'occupation' => $data['occupation'],
            'monthly_income' => (int) $data['monthly_income'],
            'heir_name' => $data['heir_name'],
            'heir_relationship' => $data['heir_relationship'],
            'heir_phone' => $data['heir_phone'] ?? null,
            'has_ktp_photo' => $data['has_ktp_photo'],
            'data_consent_at' => self::bookDateTime($office),
            'registration_date' => $office->book_date->toDateString(),
            'registered_by_id' => $user->id,
            'registered_by_name' => $user->name,
            'approved_at' => null,
            'approved_by_name' => null,
            'rejected_at' => null,
            'rejected_by_name' => null,
            'rejection_reason' => null,
            'activation_date' => null,
            'status' => 'pending_approval',
        ];

        session()->put(self::SESSION_CANDIDATES.'.'.$candidate['id'], $candidate);

        return self::hydrate($candidate);
    }

    public static function approve(array $member, User $user): void
    {
        session()->put(self::SESSION_DECISIONS.'.'.$member['id'], [
            'status' => 'approved',
            'approved_at' => self::bookDateTime($user->office),
            'approved_by_name' => $user->name,
        ]);
    }

    public static function reject(array $member, User $user, string $reason): void
    {
        session()->put(self::SESSION_DECISIONS.'.'.$member['id'], [
            'status' => 'rejected',
            'rejected_at' => self::bookDateTime($user->office),
            'rejected_by_name' => $user->name,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Riwayat keanggotaan terbaru lebih dulu.
     *
     * @param  array<int, array<string, mixed>>  $loans
     * @return array<int, array{date: Carbon, title: string, description: string, icon: string, color: string}>
     */
    public static function timeline(array $member, array $loans): array
    {
        $events = [[
            'date' => $member['data_consent_at'],
            'title' => 'Pendaftaran calon anggota',
            'description' => "Didaftarkan oleh {$member['registered_by_name']} di {$member['office_name']}. Persetujuan pemrosesan data pribadi dicatat.",
            'icon' => 'user-plus',
            'color' => 'bg-indigo-100 text-indigo-600',
        ]];

        if ($member['approved_at']) {
            $events[] = [
                'date' => $member['approved_at'],
                'title' => 'Pendaftaran disetujui',
                'description' => "Disetujui oleh {$member['approved_by_name']} (Kepala Cabang).",
                'icon' => 'check',
                'color' => 'bg-blue-100 text-blue-600',
            ];
        }

        if ($member['rejected_at']) {
            $events[] = [
                'date' => $member['rejected_at'],
                'title' => 'Pendaftaran ditolak',
                'description' => "Ditolak oleh {$member['rejected_by_name']}: {$member['rejection_reason']}",
                'icon' => 'times',
                'color' => 'bg-red-100 text-red-600',
            ];
        }

        if ($member['activation_date']) {
            $events[] = [
                'date' => $member['activation_date'],
                'title' => 'Anggota aktif',
                'description' => 'Setoran simpanan pokok Rp '.number_format(config('demo.savings.principal'), 0, ',', '.')
                    ." diterima. Nomor anggota {$member['number']} terbit, rekening simpanan pokok dan wajib dibuka.",
                'icon' => 'id-card',
                'color' => 'bg-green-100 text-green-600',
            ];
        }

        foreach ($loans as $loan) {
            $events[] = [
                'date' => $loan['disbursement_date'],
                'title' => "Pencairan {$loan['product_name']}",
                'description' => "Pinjaman {$loan['number']} sebesar Rp ".number_format($loan['principal'], 0, ',', '.')
                    ." dengan tenor {$loan['tenor_months']} bulan.",
                'icon' => 'hand-holding-usd',
                'color' => 'bg-purple-100 text-purple-600',
            ];

            if ($loan['paid_off_date']) {
                $events[] = [
                    'date' => $loan['paid_off_date'],
                    'title' => 'Pinjaman lunas',
                    'description' => "Pinjaman {$loan['number']} lunas dan jaminan dikembalikan.",
                    'icon' => 'flag-checkered',
                    'color' => 'bg-emerald-100 text-emerald-600',
                ];
            }
        }

        usort($events, fn (array $a, array $b) => $b['date'] <=> $a['date']);

        return $events;
    }

    public static function maskNik(string $nik): string
    {
        return substr($nik, 0, 4).str_repeat('*', 8).substr($nik, -4);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function generated(): array
    {
        if (self::$generated !== null) {
            return self::$generated;
        }

        $faker = FakerFactory::create('id_ID');
        $offices = Office::withoutGlobalScope(OfficeScope::class)->where('type', 'branch')->orderBy('code')->get();
        $staffByOffice = User::withoutGlobalScope(OfficeScope::class)
            ->with('roles')
            ->whereIn('office_id', $offices->pluck('id'))
            ->get()
            ->groupBy('office_id');
        [$minimum, $maximum] = config('demo.members.per_branch');
        $members = [];

        foreach ($offices as $office) {
            $faker->seed(config('demo.members.seed') + (int) $office->code);
            $staff = $staffByOffice->get($office->id, collect());
            $customerService = $staff->first(fn (User $user) => $user->hasRole('CS'));
            $branchHead = $staff->first(fn (User $user) => $user->hasRole('KCB'));
            $count = $faker->numberBetween($minimum, $maximum);

            for ($sequence = 1; $sequence <= $count; $sequence++) {
                $status = match (true) {
                    $sequence > $count - 2 => 'pending_approval',
                    $sequence === $count - 2 => 'approved',
                    default => 'active',
                };

                $member = self::fakeMember($faker, $office, $sequence, $status, $customerService, $branchHead);
                $members[$member['id']] = $member;
            }
        }

        $scenario = config('demo.scenario.duplicate_member');
        $scenarioId = (int) $scenario['office_code'] * 10000 + 1;

        if (isset($members[$scenarioId])) {
            $members[$scenarioId] = array_merge(
                $members[$scenarioId],
                collect($scenario)->only(['nik', 'name', 'gender', 'birth_place', 'birth_date'])->all()
            );
        }

        return self::$generated = $members;
    }

    /**
     * @return array<string, mixed>
     */
    private static function fakeMember(Generator $faker, Office $office, int $sequence, string $status, ?User $customerService, ?User $branchHead): array
    {
        $bookDate = $office->book_date->copy();
        $city = str_replace('Cabang ', '', $office->name);
        $gender = $faker->randomElement(['male', 'female']);
        $birthDate = $bookDate->copy()->subDays($faker->numberBetween(21 * 365, 60 * 365));

        $registrationDate = match ($status) {
            'active' => $bookDate->copy()->subDays($faker->numberBetween(45, 1800)),
            'approved' => $bookDate->copy()->subDays($faker->numberBetween(1, 3)),
            default => $bookDate->copy()->subDays($faker->numberBetween(0, 1)),
        };

        $approvedAt = $status === 'pending_approval'
            ? null
            : $registrationDate->copy()->addDay()->setTime($faker->numberBetween(9, 15), $faker->numberBetween(0, 59));

        $birthDay = (int) $birthDate->format('d') + ($gender === 'female' ? 40 : 0);

        return [
            'id' => (int) $office->code * 10000 + $sequence,
            'office_id' => $office->id,
            'office_code' => $office->code,
            'office_name' => $office->name,
            'office_book_date' => $bookDate->toDateString(),
            'number' => $status === 'active' ? $office->code.'.'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT) : null,
            'nik' => '32'.$faker->numerify('####').sprintf('%02d', $birthDay).$birthDate->format('my').(int) $office->code.sprintf('%03d', $sequence),
            'name' => $faker->name($gender),
            'gender' => $gender,
            'birth_place' => $faker->boolean(60) ? $city : $faker->city(),
            'birth_date' => $birthDate->toDateString(),
            'address' => $faker->streetAddress().', '.$city,
            'phone' => '08'.$faker->numerify('##########'),
            'occupation' => $faker->randomElement(self::OCCUPATIONS),
            'monthly_income' => $faker->numberBetween(25, 150) * 100000,
            'heir_name' => $faker->name(),
            'heir_relationship' => $faker->randomElement(self::HEIR_RELATIONSHIPS),
            'heir_phone' => '08'.$faker->numerify('##########'),
            'has_ktp_photo' => true,
            'data_consent_at' => $registrationDate->copy()->setTime(9, $faker->numberBetween(0, 59))->toDateTimeString(),
            'registration_date' => $registrationDate->toDateString(),
            'registered_by_id' => $customerService?->id,
            'registered_by_name' => $customerService->name ?? 'Customer Service',
            'approved_at' => $approvedAt?->toDateTimeString(),
            'approved_by_name' => $approvedAt ? ($branchHead->name ?? 'Kepala Cabang') : null,
            'rejected_at' => null,
            'rejected_by_name' => null,
            'rejection_reason' => null,
            'activation_date' => $status === 'active' ? $approvedAt->copy()->addDays($faker->numberBetween(0, 2))->toDateString() : null,
            'status' => $status,
        ];
    }

    /**
     * Tanggal buku aktif kantor dengan jam server, dipakai untuk waktu keputusan demo.
     */
    private static function bookDateTime(Office $office): string
    {
        return $office->book_date->copy()->setTimeFrom(now())->toDateTimeString();
    }

    /**
     * @param  array<string, mixed>  $member
     * @return array<string, mixed>
     */
    private static function hydrate(array $member): array
    {
        foreach (self::DATE_FIELDS as $field) {
            $member[$field] = $member[$field] === null ? null : Carbon::parse($member[$field]);
        }

        $member['status_label'] = self::STATUSES[$member['status']];
        $member['gender_label'] = self::GENDERS[$member['gender']];
        $member['nik_masked'] = self::maskNik($member['nik']);

        return $member;
    }
}
