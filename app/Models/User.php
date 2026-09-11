<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Scopes\OfficeScope;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable;

    protected $fillable = [
        'office_id',
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OfficeScope);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class)->withoutGlobalScope(OfficeScope::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $roleCode): bool
    {
        return $this->roles->contains('code', $roleCode);
    }

    /**
     * Peran pusat melihat data semua kantor; peran cabang hanya kantornya sendiri (PRD bagian 5.2).
     */
    public function canAccessAllOffices(): bool
    {
        return $this->roles->contains('data_scope', 'all_offices');
    }

    /**
     * Cek akses modul sesuai matriks akses. Kelola (P) mencakup semua; operasional (O) dan setujui (A) mencakup lihat (L).
     */
    public function hasAccess(string $module, string $access = 'view'): bool
    {
        $allowedAccess = match ($access) {
            'view' => ['manage', 'operate', 'approve', 'view'],
            'operate' => ['manage', 'operate'],
            'approve' => ['manage', 'approve'],
            'manage' => ['manage'],
            default => [],
        };

        return $this->roles
            ->loadMissing('permissions')
            ->pluck('permissions')
            ->flatten()
            ->contains(fn (RolePermission $permission) => $permission->module === $module
                && in_array($permission->access, $allowedAccess, true));
    }
}
