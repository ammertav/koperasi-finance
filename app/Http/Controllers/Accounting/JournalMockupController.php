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
 * Daftar dan detail jurnal versi mockup, menggantikan menu jurnal nyata M1 saat demo.
 */
class JournalMockupController extends Controller
{
    use ResolvesLedgerFilter;

    public function index(Request $request): View
    {
        [
            'office' => $selectedOffice,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ] = $this->ledgerFilter($request, defaultToMonthStart: false);

        if (! $request->filled('start_date')) {
            $startDate = $endDate->copy()->subDays(6);
        }

        [$startDate, $endDate] = AccountingMockup::clampPeriod($selectedOffice ?? Auth::user()->office, $startDate, $endDate);

        $journals = AccountingMockup::journalsFor($selectedOffice?->id, $startDate, $endDate);
        $offices = Office::orderBy('code')->get();

        return view('accounting.journalMockup', compact('journals', 'offices', 'selectedOffice', 'startDate', 'endDate'));
    }

    public function show(string $id): View
    {
        $journal = AccountingMockup::findJournal(Auth::user(), $id) ?? abort(404);

        return view('accounting.journalMockupShow', compact('journal'));
    }
}
