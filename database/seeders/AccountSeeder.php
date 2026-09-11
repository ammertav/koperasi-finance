<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Office;
use App\Services\Accounting\InterOfficeAccountService;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Bagan akun (COA) baseline KSP, sama untuk semua kantor (AKT-01). Asumsi prototype sampai COA
     * disusun bersama akuntan klien (OQ-38). Kode tiga segmen adalah akun posting; sisanya akun induk.
     * Simpanan pokok dan wajib diklasifikasikan ekuitas, simpanan sukarela dan berjangka kewajiban.
     *
     * @var array<string, string>
     */
    private const ACCOUNTS = [
        '1' => 'Aset',
        '1.1' => 'Kas dan Bank',
        '1.1.01' => 'Kas Teller',
        '1.1.02' => 'Kas Brankas',
        '1.1.03' => 'Bank',
        '1.2' => 'Pinjaman Anggota',
        '1.2.01' => 'Pinjaman Umum',
        '1.2.02' => 'Pinjaman Usaha',
        '1.9' => 'Rekening Antar Kantor',
        '2' => 'Kewajiban',
        '2.1' => 'Simpanan Anggota',
        '2.1.01' => 'Simpanan Sukarela',
        '2.1.02' => 'Simpanan Berjangka',
        '2.2' => 'Kewajiban Lain-lain',
        '2.2.01' => 'Titipan Anggota',
        '3' => 'Ekuitas',
        '3.1' => 'Simpanan Anggota',
        '3.1.01' => 'Simpanan Pokok',
        '3.1.02' => 'Simpanan Wajib',
        '3.2' => 'Cadangan',
        '3.2.01' => 'Cadangan Umum',
        '3.3' => 'Sisa Hasil Usaha',
        '3.3.01' => 'SHU Tahun Lalu',
        '3.3.02' => 'SHU Tahun Berjalan',
        '4' => 'Pendapatan',
        '4.1' => 'Pendapatan Jasa Pinjaman',
        '4.1.01' => 'Jasa Pinjaman Umum',
        '4.1.02' => 'Jasa Pinjaman Usaha',
        '4.2' => 'Pendapatan Operasional Lain',
        '4.2.01' => 'Pendapatan Provisi',
        '4.2.02' => 'Pendapatan Administrasi',
        '4.2.03' => 'Pendapatan Denda',
        '4.3' => 'Pendapatan Non-operasional',
        '4.3.01' => 'Selisih Lebih Kas',
        '5' => 'Beban',
        '5.1' => 'Beban Jasa Simpanan',
        '5.1.01' => 'Jasa Simpanan Sukarela',
        '5.2' => 'Beban Operasional',
        '5.2.01' => 'Beban Gaji dan Tunjangan',
        '5.2.02' => 'Beban Administrasi dan Umum',
        '5.3' => 'Beban Non-operasional',
        '5.3.01' => 'Selisih Kurang Kas',
    ];

    private const TYPES = [
        '1' => 'asset',
        '2' => 'liability',
        '3' => 'equity',
        '4' => 'revenue',
        '5' => 'expense',
    ];

    /**
     * Contoh akun yang dibatasi untuk kantor pusat (AKT-01).
     */
    private const HEAD_OFFICE_ONLY = ['3.2.01', '3.3.01'];

    /**
     * Run the database seeds.
     */
    public function run(InterOfficeAccountService $interOfficeAccountService): void
    {
        foreach (self::ACCOUNTS as $code => $name) {
            $code = (string) $code;
            $segments = explode('.', $code);
            $level = count($segments);
            $type = self::TYPES[$segments[0]];

            Account::updateOrCreate(['code' => $code], [
                'parent_id' => $level > 1 ? Account::where('code', implode('.', array_slice($segments, 0, -1)))->value('id') : null,
                'name' => $name,
                'type' => $type,
                'normal_balance' => in_array($type, ['asset', 'expense'], true) ? 'debit' : 'credit',
                'level' => $level,
                'is_postable' => $level === 3,
                'is_head_office_only' => in_array($code, self::HEAD_OFFICE_ONLY, true),
                'is_active' => true,
            ]);
        }

        // RAK-01: pasangan akun RAK pusat–cabang untuk setiap kantor selain pusat yang sudah ada.
        Office::where('type', '!=', 'head_office')
            ->orderBy('code')
            ->get()
            ->each(fn (Office $office) => $interOfficeAccountService->ensureAccountsFor($office));
    }
}
