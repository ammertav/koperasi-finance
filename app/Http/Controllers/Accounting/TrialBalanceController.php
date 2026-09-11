<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResolvesLedgerFilter;
use App\Models\Office;
use App\Services\Accounting\LedgerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrialBalanceController extends Controller
{
    use ResolvesLedgerFilter;

    public function __construct(private LedgerService $ledgerService) {}

    public function index(Request $request): View
    {
        [
            'office' => $selectedOffice,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ] = $this->ledgerFilter($request);

        ['rows' => $rows, 'totals' => $totals] = $this->ledgerService->trialBalance($selectedOffice?->id, $startDate, $endDate);

        $isBalanced = bccomp($totals['closing_debit'], $totals['closing_credit'], 2) === 0;

        $offices = Office::orderBy('code')->get();

        return view('accounting.trialBalance', compact('rows', 'totals', 'isBalanced', 'offices', 'selectedOffice', 'startDate', 'endDate'));
    }
}
