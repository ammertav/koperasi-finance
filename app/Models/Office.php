<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Scopes\OfficeScope;
use Database\Factories\OfficeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    /** @use HasFactory<OfficeFactory> */
    use Auditable, HasFactory;

    public const TYPES = [
        'head_office' => 'Kantor Pusat',
        'branch' => 'Cabang',
        'sub_branch' => 'Cabang Pembantu',
        'cash_office' => 'Kantor Kas',
    ];

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'type',
        'address',
        'book_date',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OfficeScope('id'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'book_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'parent_id')->withoutGlobalScope(OfficeScope::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Office::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    protected function auditOfficeId(): ?int
    {
        return $this->getKey();
    }
}
