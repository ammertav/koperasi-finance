<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Scopes\OfficeScope;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class Journal extends Model
{
    use Auditable;

    public const TRANSACTION_TYPES = [
        'opening_balance' => 'Saldo awal',
        'fund_dropping' => 'Dropping dana',
        'vault_to_teller' => 'Kas brankas ke teller',
        'teller_to_vault' => 'Kas teller ke brankas',
        'cash_shortage' => 'Selisih kurang kas',
        'cash_overage' => 'Selisih lebih kas',
        'savings_deposit' => 'Setoran simpanan',
        'savings_withdrawal' => 'Penarikan simpanan',
        'loan_disbursement' => 'Pencairan pinjaman',
        'loan_installment' => 'Angsuran pinjaman',
        'memorial' => 'Jurnal memorial',
        'reversal' => 'Pembalikan',
    ];

    public const STATUSES = [
        'draft' => 'Draf',
        'pending_approval' => 'Menunggu persetujuan',
        'posted' => 'Diposting',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'office_id',
        'reversal_of_id',
        'created_by',
        'number',
        'book_date',
        'transaction_type',
        'description',
        'source_type',
        'source_id',
        'inter_office_group',
        'total_amount',
        'status',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OfficeScope);

        // Jurnal terposting tidak boleh diubah atau dihapus; koreksi lewat jurnal pembalik (ATR-03).
        static::updating(function (Journal $journal): void {
            if ($journal->getOriginal('status') === 'posted') {
                throw new LogicException('Jurnal yang sudah diposting tidak dapat diubah. Gunakan jurnal pembalik.');
            }
        });

        static::deleting(function (Journal $journal): void {
            if ($journal->getOriginal('status') === 'posted') {
                throw new LogicException('Jurnal yang sudah diposting tidak dapat dihapus. Gunakan jurnal pembalik.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'book_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    #[Scope]
    protected function posted(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), 'posted');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class)->withoutGlobalScope(OfficeScope::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScope(OfficeScope::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(JournalDetail::class)->orderBy('id');
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'reversal_of_id')->withoutGlobalScope(OfficeScope::class);
    }

    public function reversal(): HasOne
    {
        return $this->hasOne(Journal::class, 'reversal_of_id')->withoutGlobalScope(OfficeScope::class);
    }

    /**
     * Semua jurnal dalam satu transaksi antar kantor, termasuk jurnal ini sendiri.
     */
    public function interOfficeJournals(): HasMany
    {
        return $this->hasMany(Journal::class, 'inter_office_group', 'inter_office_group')
            ->withoutGlobalScope(OfficeScope::class)
            ->orderBy('id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTransactionTypeLabelAttribute(): string
    {
        return self::TRANSACTION_TYPES[$this->transaction_type] ?? ($this->transaction_type ?? '-');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
