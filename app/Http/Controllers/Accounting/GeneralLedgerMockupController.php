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
 * Buku besar versi mockup (AKT-05).
 */
class GeneralLedgerMockupController extends Controller
{
    use ResolvesLedgerFilter;

    public function index(Request $request): View
    {
        [
            'office' => $selectedOffice,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ] = $this->ledgerFilter($request);

        [$startDate, $endDate] = AccountingMockup::clampPeriod($selectedOffice ?? Auth::user()->office, $startDate, $endDate);

        $accounts = AccountingMockup::accounts();
        $accountCode = array_key_exists((string) $request->query('account_code'), $accounts) ? $request->query('account_code') : '1.1.01';
        $account = $accounts[$accountCode];
        $ledger = AccountingMockup::ledger($accountCode, $selectedOffice?->id, $startDate, $endDate);
        $offices = Office::orderBy('code')->get();

        return view('accounting.generalLedgerMockup', compact('accounts', 'account', 'ledger', 'offices', 'selectedOffice', 'startDate', 'endDate'));
    }
}
