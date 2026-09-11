<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mode demo prototype
    |--------------------------------------------------------------------------
    |
    | Mengaktifkan pengalih peran "Masuk sebagai..." di navbar. Jangan aktifkan
    | di lingkungan produksi.
    |
    */

    'enabled' => (bool) env('APP_DEMO', false),

    'password' => env('DEMO_PASSWORD', 'password'),

    /*
    |--------------------------------------------------------------------------
    | Kantor demo
    |--------------------------------------------------------------------------
    |
    | Kode 00 adalah kantor pusat, 01–09 cabang (PRD bagian 4, BRIEF data demo).
    |
    */

    'offices' => [
        '00' => ['name' => 'Kantor Pusat', 'address' => 'Jl. Asia Afrika No. 88, Bandung'],
        '01' => ['name' => 'Cabang Bandung', 'address' => 'Jl. Soekarno-Hatta No. 412, Bandung'],
        '02' => ['name' => 'Cabang Cimahi', 'address' => 'Jl. Amir Machmud No. 57, Cimahi'],
        '03' => ['name' => 'Cabang Garut', 'address' => 'Jl. Ciledug No. 120, Garut'],
        '04' => ['name' => 'Cabang Tasikmalaya', 'address' => 'Jl. HZ Mustofa No. 33, Tasikmalaya'],
        '05' => ['name' => 'Cabang Cirebon', 'address' => 'Jl. Siliwangi No. 76, Cirebon'],
        '06' => ['name' => 'Cabang Sumedang', 'address' => 'Jl. Prabu Geusan Ulun No. 14, Sumedang'],
        '07' => ['name' => 'Cabang Subang', 'address' => 'Jl. Otto Iskandardinata No. 21, Subang'],
        '08' => ['name' => 'Cabang Purwakarta', 'address' => 'Jl. Kolonel Kornel Singawinata No. 9, Purwakarta'],
        '09' => ['name' => 'Cabang Sukabumi', 'address' => 'Jl. Jenderal Sudirman No. 150, Sukabumi'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Matriks wewenang (ORG-06, OQ-14)
    |--------------------------------------------------------------------------
    |
    | Batas nominal persetujuan per peran per jenis transaksi. null berarti
    | tanpa batas. Nilai pinjaman dari BRIEF; nilai lain asumsi prototype.
    |
    */

    'authority_limits' => [
        'KCB' => [
            'loan_approval' => '25000000',
            'withdrawal' => '50000000',
            'reversal' => '10000000',
            'penalty_waiver' => '1000000',
            'cash_difference' => null,
        ],
        'KKR' => [
            'loan_approval' => '100000000',
        ],
        'MGR' => [
            'loan_approval' => null,
            'withdrawal' => null,
            'reversal' => null,
            'penalty_waiver' => null,
        ],
        'KAK' => [
            'memorial_journal' => null,
            'reversal' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Saldo awal demo
    |--------------------------------------------------------------------------
    |
    | Saldo bank kantor pusat dan dropping dana awal ke kas brankas setiap
    | cabang. Diposting lewat JournalService oleh Demo\OpeningBalanceSeeder.
    |
    */

    'opening_balance' => [
        'head_office_bank' => '2000000000',
        'branch_dropping' => '150000000',
    ],

    /*
    |--------------------------------------------------------------------------
    | Data contoh prototype tampilan (app/Mockups)
    |--------------------------------------------------------------------------
    |
    | Anggota dibuat deterministik per cabang dengan Faker seed tetap. Nominal
    | simpanan dan produk pinjaman mengikuti asumsi angka di BRIEF.
    |
    */

    'members' => [
        'seed' => 2026,
        'per_branch' => [60, 120],
    ],

    'savings' => [
        'principal' => 100000,
        'mandatory_monthly' => 50000,
        'voluntary_minimum_deposit' => 10000,
        'voluntary_minimum_balance' => 25000,
        'voluntary_annual_rate' => 3,
        // Penarikan di atas batas ini menunggu otorisasi (SIM-05), lalu naik mengikuti matriks wewenang.
        'teller_withdrawal_limit' => 5000000,
        'history_months' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Kas teller dan brankas (M05)
    |--------------------------------------------------------------------------
    |
    | Jurnal rinci mockup mencakup detail_days hari terakhir; transaksi sebelumnya
    | diringkas menjadi saldo awal. Saldo brankas awal dalam juta rupiah.
    |
    */

    'cash' => [
        'teller_opening_balance' => 20000000,
        'vault_opening_millions' => [120, 250],
        'detail_days' => 30,
        'head_office_reserve' => 500000000,
    ],

    'loan_products' => [
        'PUM' => ['name' => 'Pinjaman Umum', 'interest_method' => 'flat', 'annual_rate' => 18, 'min_tenor' => 6, 'max_tenor' => 36],
        'PUS' => ['name' => 'Pinjaman Usaha', 'interest_method' => 'annuity', 'annual_rate' => 18, 'min_tenor' => 12, 'max_tenor' => 60],
    ],

    'loan_fees' => [
        'provision_rate' => 1,
        'admin_fee' => 50000,
    ],

    'data_consent_text' => 'Saya menyetujui koperasi mengumpulkan, menyimpan, dan memproses data pribadi saya untuk keperluan keanggotaan dan layanan simpan pinjam, sesuai UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi.',

    /*
    |--------------------------------------------------------------------------
    | Skenario naskah demo
    |--------------------------------------------------------------------------
    |
    | Babak 1: anggota aktif Cabang 07 dengan pinjaman berjalan. NIK ini dipakai
    | CS Cabang 03 saat mendaftarkan calon anggota agar pendaftaran tertahan.
    |
    */

    'scenario' => [
        'duplicate_member' => [
            'office_code' => '07',
            'nik' => '3213054708850003',
            'name' => 'Siti Rahmawati',
            'gender' => 'female',
            'birth_place' => 'Subang',
            'birth_date' => '1985-08-07',
        ],
    ],

];
