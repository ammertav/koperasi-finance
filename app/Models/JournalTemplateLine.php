<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalTemplateLine extends Model
{
    use Auditable;

    /**
     * Nama nominal yang dikirim transaksi ke template.
     */
    public const AMOUNT_KEYS = [
        'amount' => 'Nominal transaksi',
        'principal' => 'Pokok pinjaman',
        'interest' => 'Jasa pinjaman',
        'penalty' => 'Denda',
        'provision_fee' => 'Provisi',
        'admin_fee' => 'Administrasi',
        'net_disbursement' => 'Dana diterima anggota',
        'total_payment' => 'Total pembayaran',
    ];

    /**
     * origin = kantor tempat transaksi dilakukan; destination = kantor pemilik rekening atau penerima dana.
     * Jika keduanya berbeda, jurnal RAK dibentuk otomatis (RAK-02).
     */
    public const OFFICE_ROLES = [
        'origin' => 'Kantor transaksi',
        'destination' => 'Kantor pemilik/penerima',
    ];

    protected $fillable = [
        'journal_template_id',
        'account_id',
        'side',
        'amount_key',
        'office_role',
        'description',
        'sort_order',
    ];

    public function journalTemplate(): BelongsTo
    {
        return $this->belongsTo(JournalTemplate::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
