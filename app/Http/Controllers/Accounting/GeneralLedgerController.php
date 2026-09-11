<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResolvesLedgerFilter;
use App\Models\Account;
use App\Models\Office;
use App\Services\Accounting\LedgerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneralLedgerController extends Controller
{
    use ResolvesLedgerFilter;

    public function __construct(private LedgerService $ledgerService) {}

    public function index(Request $request): View
    {
        $data = $request->validate([
            'account_id' => 'nullable|integer|exists:accounts,id',
        ]);

        [
            'office' => $selectedOffice,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ] = $this->ledgerFilter($request);

        $accounts = Account::where('is_postable', true)->orderBy('code')->get();

        $account = ! empty($data['account_id'])
            ? $accounts->firstWhere('id', (int) $data['account_id'])
            : $accounts->first();

        $ledger = $account
            ? $this->ledgerService->generalLedger($account, $selectedOffice?->id, $startDate, $endDate)
            : null;

        $offices = Office::orderBy('code')->get();

        return view('accounting.generalLedger', compact('accounts', 'account', 'ledger', 'offices', 'selectedOffice', 'startDate', 'endDate'));
    }
}
