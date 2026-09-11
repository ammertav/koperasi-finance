<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Penghitung nomor urut dokumen per kantor, jenis dokumen, dan periode (ATR-07).
 * Tidak memakai OfficeScope karena penomoran bisa berjalan untuk kantor lain dalam transaksi antar kantor.
 */
class DocumentSequence extends Model
{
    protected $fillable = [
        'office_id',
        'document_type',
        'period',
        'last_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_number' => 'integer',
        ];
    }
}
