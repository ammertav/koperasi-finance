<?php

namespace App\Models;

use App\Models\Scopes\OfficeScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class JournalDetail extends Model
{
    protected $fillable = [
        'journal_id',
        'account_id',
        'office_id',
        'book_date',
        'debit',
        'credit',
        'description',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OfficeScope);

        // Baris jurnal terposting tidak boleh diubah atau dihapus (ATR-03).
        $guardPostedJournal = function (JournalDetail $detail): void {
            $journalStatus = Journal::withoutGlobalScope(OfficeScope::class)
                ->whereKey($detail->getOriginal('journal_id'))
                ->value('status');

            if ($journalStatus === 'posted') {
                throw new LogicException('Baris jurnal yang sudah diposting tidak dapat diubah atau dihapus.');
            }
        };

        static::updating($guardPostedJournal);
        static::deleting($guardPostedJournal);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'book_date' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class)->withoutGlobalScope(OfficeScope::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class)->withoutGlobalScope(OfficeScope::class);
    }
}
