<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use Auditable, HasFactory;

    public const LOCATIONS = [
        'head_office' => 'Pusat',
        'branch' => 'Cabang',
    ];

    public const DATA_SCOPES = [
        'own_office' => 'Kantor sendiri',
        'all_offices' => 'Semua kantor',
    ];

    protected $fillable = [
        'code',
        'name',
        'location',
        'data_scope',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    public function authorityLimits(): HasMany
    {
        return $this->hasMany(AuthorityLimit::class);
    }
}
