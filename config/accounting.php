<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rekening Antar Kantor (RAK-01)
    |--------------------------------------------------------------------------
    |
    | Akun RAK dibuat otomatis di bawah akun induk ini dengan kode
    | {kode induk}.{kode kantor lawan}. Buku kantor pusat memakai
    | "RAK {nama cabang}", buku cabang memakai "RAK Kantor Pusat".
    |
    */

    'inter_office_parent_code' => '1.9',

];
