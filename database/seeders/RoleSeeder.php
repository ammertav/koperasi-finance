<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Peran dan matriks akses PRD bagian 5. Kolom matriks berurutan M01–M10;
     * P = kelola, O = operasional, A = setujui, L = lihat, - = tanpa akses.
     *
     * @var array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    private const ROLES = [
        'ADM' => ['Admin Sistem', 'head_office', 'all_offices', 'P - - - - - - - - L'],
        'MGR' => ['Manajer Pusat', 'head_office', 'all_offices', 'L L A A L L A A L L'],
        'KAK' => ['Kepala Keuangan dan Akuntansi', 'head_office', 'all_offices', 'L L L L A P P P L L'],
        'SAK' => ['Staf Akuntansi Pusat', 'head_office', 'all_offices', '- L L L O O O O L -'],
        'KKR' => ['Komite Kredit', 'head_office', 'all_offices', '- L - A - - - - L -'],
        'AUD' => ['Pengawas dan Auditor Internal', 'head_office', 'all_offices', 'L L L L L L L L L L'],
        'PGR' => ['Pengurus', 'head_office', 'all_offices', '- - - - - - - A L -'],
        'KCB' => ['Kepala Cabang', 'branch', 'own_office', '- A A A A L L O L L'],
        'CS' => ['Customer Service', 'branch', 'own_office', '- O O O - - - - L -'],
        'TLR' => ['Teller', 'branch', 'own_office', '- L O O O - - - L -'],
        'ANK' => ['Analis Kredit', 'branch', 'own_office', '- L L O - - - - L -'],
        'ADC' => ['Admin dan Pembukuan Cabang', 'branch', 'own_office', '- L L O O O L O L -'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = array_keys(RolePermission::MODULES);
        $accessByCode = array_flip(RolePermission::ACCESS);

        foreach (self::ROLES as $code => [$name, $location, $dataScope, $matrix]) {
            $role = Role::updateOrCreate(['code' => $code], [
                'name' => $name,
                'location' => $location,
                'data_scope' => $dataScope,
            ]);

            foreach (explode(' ', $matrix) as $index => $accessCode) {
                if ($accessCode === '-') {
                    continue;
                }

                $role->permissions()->updateOrCreate(
                    ['module' => $modules[$index]],
                    ['access' => $accessByCode[$accessCode]]
                );
            }
        }
    }
}
