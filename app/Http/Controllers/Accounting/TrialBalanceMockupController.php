<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResolvesLedgerFilter;
use App\Mockups\AccountingMockup;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Neraca saldo versi mockup dengan pencocokan buku pembantu (AKT-05, AKT-08).
 */
class TrialBalanceMockupController extends Controller
{
    use ResolvesLedgerFilter;

    public function index(Request $request): View
    {
        [
            'office' => $selectedOffice,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ] = $this->ledgerFilter($request);

        $periodOffice = $selectedOffice ?? Auth::user()->office;
        [$startDate, $endDate] = AccountingMockup::clampPeriod($periodOffice, $startDate, $endDate);

        ['rows' => $rows, 'totals' => $totals, 'is_balanced' => $isBalanced] = AccountingMockup::trialBalance($selectedOffice?->id, $startDate, $endDate);

        // Buku pembantu hanya tersedia per tanggal buku aktif.
        $reconciliation = $endDate->isSameDay($periodOffice->book_date)
            ? AccountingMockup::subledgerReconciliation($selectedOffice?->id, $endDate)
            : [];

        $offices = Office::orderBy('code')->get();

        return view('accounting.trialBalanceMockup', compact('rows', 'totals', 'isBalanced', 'reconciliation', 'offices', 'selectedOffice', 'startDate', 'endDate'));
    }
}
