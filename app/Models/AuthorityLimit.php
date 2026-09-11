<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorityLimit extends Model
{
    use Auditable;

    public const TRANSACTION_TYPES = [
        'withdrawal' => 'Penarikan simpanan',
        'loan_approval' => 'Persetujuan pinjaman',
        'memorial_journal' => 'Jurnal memorial',
        'reversal' => 'Pembalikan transaksi',
        'cash_difference' => 'Selisih kas',
        'penalty_waiver' => 'Penghapusan denda',
    ];

    protected $fillable = [
        'role_id',
        'transaction_type',
        'max_amount',
        'is_unlimited',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_amount' => 'decimal:2',
            'is_unlimited' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
