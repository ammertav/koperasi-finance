<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    use Auditable;

    /**
     * Urutan modul mengikuti matriks akses PRD bagian 5.2.
     */
    public const MODULES = [
        'organization' => 'M01 Organisasi',
        'member' => 'M02 Anggota',
        'savings' => 'M03 Simpanan',
        'loan' => 'M04 Pinjaman',
        'cash' => 'M05 Kas',
        'accounting' => 'M06 Akuntansi',
        'inter_office' => 'M07 Antar Kantor',
        'closing' => 'M08 Tutup Buku',
        'report' => 'M09 Laporan',
        'audit' => 'M10 Audit',
    ];

    public const ACCESS = [
        'manage' => 'P',
        'operate' => 'O',
        'approve' => 'A',
        'view' => 'L',
    ];

    protected $fillable = [
        'role_id',
        'module',
        'access',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
