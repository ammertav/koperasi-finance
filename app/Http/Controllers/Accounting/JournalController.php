<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResolvesLedgerFilter;
use App\Models\Journal;
use App\Models\Office;
use App\Services\Accounting\JournalService;
use App\Services\Organization\AuthorityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JournalController extends Controller
{
    use ResolvesLedgerFilter;

    public function __construct(
        private JournalService $journalService,
        private AuthorityService $authorityService,
    ) {}

    public function index(Request $request): View
    {
        [
            'office' => $selectedOffice,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ] = $this->ledgerFilter($request, defaultToMonthStart: false);

        $journals = Journal::with(['office', 'reversal'])
            ->when($selectedOffice, fn ($query, Office $office) => $query->where('office_id', $office->id))
            ->where('book_date', '>=', $startDate->toDateString())
            ->where('book_date', '<', $endDate->copy()->addDay()->toDateString())
            ->orderByDesc('book_date')
            ->orderByDesc('id')
            ->get();

        $offices = Office::orderBy('code')->get();

        return view('accounting.journal', compact('journals', 'offices', 'selectedOffice', 'startDate', 'endDate'));
    }

    public function show(string $id): View
    {
        $journal = Journal::with(['office', 'creator', 'details.account', 'reversal', 'reversalOf', 'interOfficeJournals.office'])
            ->findOrFail($id);

        $reversalCheck = $this->journalService->canReverse($journal);

        return view('accounting.journalShow', compact('journal', 'reversalCheck'));
    }

    public function reverse(Request $request, string $id): RedirectResponse
    {
        $journal = Journal::findOrFail($id);

        $data = $request->validate([
            'reason' => 'required|string|max:200',
        ]);

        $user = auth()->user();

        // Maker-checker: pembuat transaksi tidak boleh mengotorisasi koreksinya sendiri (ATR-05).
        if ((int) $journal->created_by === $user->id) {
            return back()->withErrors(['msg' => 'Pembuat jurnal tidak boleh membalik jurnalnya sendiri.'])->withInput();
        }

        $reversalCheck = $this->journalService->canReverse($journal);

        if (! $reversalCheck['allowed']) {
            return back()->withErrors(['msg' => $reversalCheck['message']])->withInput();
        }

        $authorityCheck = $this->authorityService->check($user, 'reversal', $journal->total_amount);

        if (! $authorityCheck['allowed']) {
            return back()->withErrors(['msg' => $authorityCheck['message']])->withInput();
        }

        DB::beginTransaction();

        try {
            $reversal = $this->journalService->reverse($journal, $data['reason'], $user);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['msg' => 'Pembalikan gagal: '.$e->getMessage()])->withInput();
        }

        return redirect(route('detailJournal', $reversal->id))->with('success', 'Jurnal pembalik berhasil dibuat!');
    }
}
