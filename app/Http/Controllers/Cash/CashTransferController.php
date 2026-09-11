<?php

namespace App\Http\Controllers\Cash;

use App\Http\Controllers\Controller;
use App\Mockups\CashMockup;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Perpindahan kas teller dan brankas dengan konfirmasi pihak kedua (KAS-02).
 */
class CashTransferController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $offices = $user->canAccessAllOffices() ? Office::where('type', 'branch')->orderBy('code')->get() : collect([$user->office]);
        $office = $request->filled('office_id') ? Office::findOrFail($request->integer('office_id')) : ($user->office->type === 'branch' ? $user->office : $offices->first());

        $transfers = array_map(
            fn (array $transfer) => [...$transfer, 'can_confirm' => CashMockup::canConfirm($transfer, $user)],
            CashMockup::transfers($office->id)
        );

        $cashSession = $office->id === $user->office_id ? CashMockup::cashSession($user) : null;
        $tellerBalance = $cashSession ? CashMockup::tellerBalance($user) : null;
        $vaultBalance = CashMockup::vaultBalance($office);
        $directions = CashMockup::TRANSFER_DIRECTIONS;

        return view('cash.cashTransfer', compact('offices', 'office', 'transfers', 'cashSession', 'tellerBalance', 'vaultBalance', 'directions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['amount' => str_replace('.', '', (string) $request->input('amount'))]);

        $data = $request->validate([
            'direction' => 'required|in:vault_to_teller,teller_to_vault',
            'amount' => 'required|integer|min:1',
            'note' => 'nullable|string|max:200',
        ]);

        $user = auth()->user();
        $amount = (int) $data['amount'];

        if (! CashMockup::cashSession($user)) {
            return back()->withErrors(['msg' => 'Sesi kas teller belum dibuka.'])->withInput();
        }

        $check = $this->balanceCheck($user, $data['direction'], $amount);

        if (! $check['allowed']) {
            return back()->withErrors(['msg' => $check['message']])->withInput();
        }

        CashMockup::addTransfer($user, $data['direction'], $amount, $data['note'] ?? null);

        return redirect(route('cashTransfer'))->with('success', 'Perpindahan kas diajukan dan menunggu konfirmasi pemegang brankas!');
    }

    public function confirm(string $id): RedirectResponse
    {
        $user = auth()->user();
        $transfer = CashMockup::findTransfer($user, (int) $id) ?? abort(404);

        if (! CashMockup::canConfirm($transfer, $user)) {
            return back()->withErrors(['msg' => 'Perpindahan kas hanya dapat dikonfirmasi pemegang brankas di kantor yang sama, bukan oleh pengaju.']);
        }

        $requester = User::find($transfer['requested_by_id']);
        $check = $requester ? $this->balanceCheck($requester, $transfer['direction'], $transfer['amount'], $transfer['id']) : ['allowed' => false, 'message' => 'Pengaju tidak ditemukan.'];

        if (! $check['allowed']) {
            return back()->withErrors(['msg' => $check['message']]);
        }

        CashMockup::confirmTransfer($transfer, $user);

        return back()->with('success', 'Perpindahan kas dikonfirmasi dan jurnal terbentuk otomatis!');
    }

    public function reject(Request $request, string $id): RedirectResponse
    {
        $user = auth()->user();
        $transfer = CashMockup::findTransfer($user, (int) $id) ?? abort(404);

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:200',
        ]);

        if (! CashMockup::canConfirm($transfer, $user)) {
            return back()->withErrors(['msg' => 'Perpindahan kas hanya dapat ditolak pemegang brankas di kantor yang sama, bukan oleh pengaju.']);
        }

        CashMockup::rejectTransfer($transfer, $user, $data['rejection_reason']);

        return back()->with('success', 'Perpindahan kas ditolak!');
    }

    /**
     * Pengambilan dibatasi saldo brankas; penyetoran dibatasi saldo teller dikurangi penyetoran yang belum dikonfirmasi.
     *
     * @return array{allowed: bool, message: string|null}
     */
    private function balanceCheck(User $teller, string $direction, int $amount, ?int $ignoredTransferId = null): array
    {
        if ($direction === 'vault_to_teller') {
            return $amount > CashMockup::vaultBalance($teller->office)
                ? ['allowed' => false, 'message' => 'Saldo kas brankas tidak mencukupi.']
                : ['allowed' => true, 'message' => null];
        }

        $pending = collect(CashMockup::transfers($teller->office_id))
            ->where('status', 'pending_confirmation')
            ->where('direction', 'teller_to_vault')
            ->where('requested_by_id', $teller->id)
            ->reject(fn (array $transfer) => $transfer['id'] === $ignoredTransferId)
            ->sum('amount');

        return $amount > CashMockup::tellerBalance($teller) - $pending
            ? ['allowed' => false, 'message' => 'Saldo kas teller tidak mencukupi untuk disetor ke brankas.']
            : ['allowed' => true, 'message' => null];
    }
}
