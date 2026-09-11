<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalTemplate extends Model
{
    use Auditable;

    protected $fillable = [
        'code',
        'transaction_type',
        'product_code',
        'name',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalTemplateLine::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getTransactionTypeLabelAttribute(): string
    {
        return Journal::TRANSACTION_TYPES[$this->transaction_type] ?? $this->transaction_type;
    }
}
