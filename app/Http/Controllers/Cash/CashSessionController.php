<?php

namespace App\Http\Controllers\Cash;

use App\Http\Controllers\Controller;
use App\Mockups\CashMockup;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Sesi kas teller versi prototype tampilan (KAS-01, KAS-11).
 */
class CashSessionController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $offices = $user->canAccessAllOffices() ? Office::where('type', 'branch')->orderBy('code')->get() : collect([$user->office]);

        // OfficeScope membuat kantor lain tidak ditemukan (404) untuk peran cabang.
        $office = $request->filled('office_id') ? Office::findOrFail($request->integer('office_id')) : ($user->office->type === 'branch' ? $user->office : $offices->first());

        $sessions = array_map(
            fn (array $cashSession) => [...$cashSession, ...CashMockup::tellerMutations($cashSession)],
            CashMockup::sessionsForOffice($office)
        );

        $canOpen = $user->hasRole('TLR') && $user->hasAccess('cash', 'operate') && $office->id === $user->office_id && ! CashMockup::cashSession($user);
        $vaultBalance = CashMockup::vaultBalance($office);
        $denominations = CashMockup::DENOMINATIONS;
        $suggestedDenominations = CashMockup::suggestedOpeningDenominations();

        return view('cash.cashSession', compact('offices', 'office', 'sessions', 'canOpen', 'vaultBalance', 'denominations', 'suggestedDenominations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'denominations' => 'required|array',
            'denominations.*' => 'nullable|integer|min:0|max:100000',
        ]);

        $user = auth()->user();

        if (! $user->hasRole('TLR')) {
            return back()->withErrors(['msg' => 'Hanya teller yang dapat membuka sesi kas.'])->withInput();
        }

        if (CashMockup::cashSession($user)) {
            return back()->withErrors(['msg' => 'Sesi kas Anda untuk tanggal buku ini sudah dibuka.'])->withInput();
        }

        $denominations = collect(CashMockup::DENOMINATIONS)
            ->map(fn (array $denomination, string $key) => (int) ($data['denominations'][$key] ?? 0))
            ->all();
        $amount = CashMockup::denominationTotal($denominations);

        if ($amount <= 0) {
            return back()->withErrors(['msg' => 'Saldo awal sesi kas harus lebih dari nol.'])->withInput();
        }

        if ($amount > CashMockup::vaultBalance($user->office)) {
            return back()->withErrors(['msg' => 'Saldo awal melebihi saldo kas brankas cabang.'])->withInput();
        }

        CashMockup::openSession($user, $denominations);

        return redirect(route('cashSession'))
            ->with('success', 'Sesi kas dibuka dengan saldo awal Rp '.number_format($amount, 0, ',', '.').' dari brankas!');
    }
}
