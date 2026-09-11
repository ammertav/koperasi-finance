<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Scopes\OfficeScope;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use Auditable, HasFactory;

    public const TYPES = [
        'asset' => 'Aset',
        'liability' => 'Kewajiban',
        'equity' => 'Ekuitas',
        'revenue' => 'Pendapatan',
        'expense' => 'Beban',
    ];

    public const NORMAL_BALANCES = [
        'debit' => 'Debit',
        'credit' => 'Kredit',
    ];

    protected $fillable = [
        'parent_id',
        'counterpart_office_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'level',
        'is_postable',
        'is_head_office_only',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_postable' => 'boolean',
            'is_head_office_only' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Kantor lawan untuk akun Rekening Antar Kantor (RAK-01). Null untuk akun biasa.
     */
    public function counterpartOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'counterpart_office_id')->withoutGlobalScope(OfficeScope::class);
    }

    public function journalDetails(): HasMany
    {
        return $this->hasMany(JournalDetail::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isInterOffice(): bool
    {
        return $this->counterpart_office_id !== null;
    }
}
