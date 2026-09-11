<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Format penomoran dokumen (ATR-07)
    |--------------------------------------------------------------------------
    |
    | Nomor unik di seluruh koperasi dan memuat kode kantor. Placeholder:
    | {prefix}, {office} (kode kantor), {period} (tanggal buku sesuai format
    | `period`), {sequence} (nomor urut per kantor per periode).
    |
    */

    'journal' => [
        'prefix' => 'JU',
        'format' => '{prefix}-{office}-{period}-{sequence}',
        'period' => 'Ymd',
        'padding' => 4,
    ],

];
