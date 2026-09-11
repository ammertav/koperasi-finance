<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Mockups\CashMockup;
use App\Mockups\MemberMockup;
use App\Mockups\SavingsMockup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Aktivasi anggota lewat setoran simpanan pokok di teller (AGT-03, SIM-02).
 */
class MemberActivationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $members = array_values(array_filter(MemberMockup::forUser($user), fn (array $member) => $member['status'] === 'approved'));
        $cashSession = CashMockup::cashSession($user);
        $principalSavings = config('demo.savings.principal');

        return view('savings.memberActivation', compact('members', 'cashSession', 'principalSavings'));
    }

    public function store(string $id): RedirectResponse
    {
        $user = auth()->user();
        $member = MemberMockup::find($user, (int) $id) ?? abort(404);

        if (! CashMockup::cashSession($user)) {
            return back()->withErrors(['msg' => 'Buka sesi kas teller terlebih dahulu sebelum menerima setoran simpanan pokok.']);
        }

        if ($member['status'] !== 'approved') {
            return back()->withErrors(['msg' => 'Hanya calon anggota yang sudah disetujui kepala cabang yang dapat diaktifkan.']);
        }

        $member = MemberMockup::activate($member, $user);
        $principalAccount = collect(SavingsMockup::forMember($member))->firstWhere('product_code', 'SP');
        $transaction = SavingsMockup::addTransaction($principalAccount, 'deposit', config('demo.savings.principal'), $user, null, true);

        return redirect(route('detailSavingsTransaction', $transaction['id']))
            ->with('success', "Anggota aktif dengan nomor {$member['number']}. Rekening simpanan pokok dan wajib sudah dibuka!");
    }
}
