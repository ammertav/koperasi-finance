<?php

namespace App\Http\Traits;

use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait ResolvesLedgerFilter
{
    /**
     * Filter kantor dan rentang tanggal untuk halaman jurnal, buku besar, dan neraca saldo.
     * Kantor null berarti konsolidasi dan hanya berlaku untuk peran berlingkup semua kantor;
     * peran cabang selalu terkunci ke kantornya. Tanggal default mengikuti tanggal buku kantor.
     *
     * @return array{office: Office|null, start_date: Carbon, end_date: Carbon}
     */
    protected function ledgerFilter(Request $request, bool $defaultToMonthStart = true): array
    {
        $data = $request->validate([
            'office_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = $request->user();

        // OfficeScope membuat kantor lain tidak ditemukan (404) untuk peran cabang.
        $office = match (true) {
            ! empty($data['office_id']) => Office::findOrFail($data['office_id']),
            $user->canAccessAllOffices() => null,
            default => $user->office,
        };

        $bookDate = ($office ?? $user->office)->book_date->copy();

        $endDate = ! empty($data['end_date']) ? Carbon::parse($data['end_date']) : $bookDate->copy();
        $startDate = match (true) {
            ! empty($data['start_date']) => Carbon::parse($data['start_date']),
            $defaultToMonthStart => $endDate->copy()->startOfMonth(),
            default => $endDate->copy(),
        };

        return ['office' => $office, 'start_date' => $startDate, 'end_date' => $endDate];
    }
}
