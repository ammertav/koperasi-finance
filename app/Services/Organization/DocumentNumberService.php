<?php

namespace App\Services\Organization;

use App\Models\DocumentSequence;
use App\Models\Office;
use Carbon\CarbonInterface;
use DomainException;

class DocumentNumberService
{
    /**
     * Ambil nomor dokumen berikutnya sesuai format di config/numbering.php (ATR-07).
     * Wajib dipanggil di dalam DB transaction agar kunci baris urutan berlaku sampai commit.
     */
    public function next(string $documentType, Office $office, CarbonInterface $date): string
    {
        $config = config("numbering.{$documentType}")
            ?? throw new DomainException("Format penomoran {$documentType} belum dikonfigurasi.");

        $period = $date->format($config['period']);
        $now = now();

        DocumentSequence::insertOrIgnore([
            'office_id' => $office->id,
            'document_type' => $documentType,
            'period' => $period,
            'last_number' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sequence = DocumentSequence::where('office_id', $office->id)
            ->where('document_type', $documentType)
            ->where('period', $period)
            ->lockForUpdate()
            ->firstOrFail();

        $sequence->increment('last_number');

        return strtr($config['format'], [
            '{prefix}' => $config['prefix'],
            '{office}' => $office->code,
            '{period}' => $period,
            '{sequence}' => str_pad((string) $sequence->last_number, $config['padding'], '0', STR_PAD_LEFT),
        ]);
    }
}
