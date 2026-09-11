<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Mockups\AccountingMockup;
use App\Mockups\CashMockup;
use App\Mockups\SavingsMockup;
use App\Models\User;
use App\Services\Organization\AuthorityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Setoran dan penarikan tunai di teller versi prototype tampilan (SIM-03, SIM-05, SIM-06). Transaksi disimpan di
 * session demo dan jurnalnya dibentuk AccountingMockup.
 */
class SavingsTransactionController extends Controller
{
    public function __construct(private AuthorityService $authorityService) {}

    public function index(): View
    {
        $user = Auth::user();
        $transactions = SavingsMockup::transactions($user->canAccessAllOffices() ? null : $user->office_id);
        $posted = collect($transactions)->where('status', 'posted');

        $summary = [
            'deposit_total' => $posted->where('type', 'deposit')->sum('amount'),
            'deposit_count' => $posted->where('type', 'deposit')->count(),
            'withdrawal_total' => $posted->where('type', 'withdrawal')->sum('amount'),
            'withdrawal_count' => $posted->where('type', 'withdrawal')->count(),
            'pending_count' => collect($transactions)->where('status', 'pending_authorization')->count(),
        ];

        $cashSession = CashMockup::cashSession($user);
        $showOffice = $user->canAccessAllOffices();

        return view('savings.savingsTransaction', compact('transactions', 'summary', 'cashSession', 'showOffice'));
    }

    public function create(): View|RedirectResponse
    {
        $user = Auth::user();
        $cashSession = CashMockup::cashSession($user);

        if (! $cashSession) {
            return redirect(route('cashSession'))->withErrors(['msg' => 'Buka sesi kas teller terlebih dahulu sebelum melayani setoran dan penarikan.']);
        }

        $tellerBalance = CashMockup::tellerBalance($user);
        $limits = [
            'teller_withdrawal' => config('demo.savings.teller_withdrawal_limit'),
            'branch_head_withdrawal' => (int) config('demo.authority_limits.KCB.withdrawal'),
            'mandatory_monthly' => config('demo.savings.mandatory_monthly'),
            'voluntary_minimum_deposit' => config('demo.savings.voluntary_minimum_deposit'),
        ];
        $accountCodes = SavingsMockup::ACCOUNT_CODES;

        return view('savings.savingsTransactionCreate', compact('cashSession', 'tellerBalance', 'limits', 'accountCodes'));
    }

    public function searchAccount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'keyword' => 'required|string|min:3|max:100',
        ]);

        $accounts = array_map(fn (array $account) => [
            'number' => $account['number'],
            'product_code' => $account['product_code'],
            'product_name' => $account['product_name'],
            'member_name' => $account['member_name'],
            'member_number' => $account['member_number'],
            'balance' => $account['balance'],
            'available_balance' => $account['product_code'] === 'SS' ? max(0, SavingsMockup::availableBalance($account)) : 0,
        ], SavingsMockup::search(auth()->user(), $data['keyword']));

        return response()->json(['accounts' => $accounts]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['amount' => str_replace('.', '', (string) $request->input('amount'))]);

        $data = $request->validate([
            'account_number' => 'required|string|max:20',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $amount = (int) $data['amount'];

        if (! CashMockup::cashSession($user)) {
            return back()->withErrors(['msg' => 'Sesi kas teller belum dibuka.'])->withInput();
        }

        $account = SavingsMockup::findAccount($user, $data['account_number']);

        if (! $account) {
            return back()->withErrors(['msg' => 'Rekening tidak ditemukan di kantor Anda.'])->withInput();
        }

        $check = SavingsMockup::transactionCheck($account, $data['type'], $amount);

        if (! $check['allowed']) {
            return back()->withErrors(['msg' => $check['message']])->withInput();
        }

        if ($data['type'] === 'withdrawal' && ! $check['requires_authorization'] && $amount > CashMockup::tellerBalance($user)) {
            return back()->withErrors(['msg' => 'Kas teller tidak mencukupi. Ambil tambahan kas dari brankas terlebih dahulu.'])->withInput();
        }

        $transaction = SavingsMockup::addTransaction($account, $data['type'], $amount, $user, $check['authorization_role']);

        $message = $check['requires_authorization']
            ? 'Penarikan melebihi batas teller dan menunggu otorisasi '.($check['authorization_role'] === 'KCB' ? 'kepala cabang' : 'manajer pusat').'!'
            : 'Transaksi berhasil diposting dan jurnal terbentuk otomatis!';

        return redirect(route('detailSavingsTransaction', $transaction['id']))->with('success', $message);
    }

    public function show(string $id): View
    {
        $user = Auth::user();
        $transaction = SavingsMockup::findTransaction($user, (int) $id) ?? abort(404);
        $journal = AccountingMockup::journalForReference($transaction['number']);

        $canAuthorize = $transaction['status'] === 'pending_authorization'
            && $user->hasAccess('savings', 'approve')
            && $transaction['teller_id'] !== $user->id;
        $authorityCheck = $canAuthorize ? $this->authorityService->check($user, 'withdrawal', (string) $transaction['amount']) : null;

        return view('savings.savingsTransactionShow', compact('transaction', 'journal', 'canAuthorize', 'authorityCheck'));
    }

    public function print(string $id): View
    {
        $transaction = SavingsMockup::findTransaction(Auth::user(), (int) $id) ?? abort(404);
        abort_unless($transaction['status'] === 'posted', 404);

        $journal = AccountingMockup::journalForReference($transaction['number']);

        return view('savings.savingsTransactionPrint', compact('transaction', 'journal'));
    }

    public function approve(string $id): RedirectResponse
    {
        $user = auth()->user();
        $transaction = SavingsMockup::findTransaction($user, (int) $id) ?? abort(404);
        $check = $this->authorizationCheck($transaction, $user);

        if (! $check['allowed']) {
            return back()->withErrors(['msg' => $check['message']]);
        }

        $teller = User::find($transaction['teller_id']);

        if (! $teller || CashMockup::tellerBalance($teller) < $transaction['amount']) {
            return back()->withErrors(['msg' => 'Kas teller tidak mencukupi untuk membayar penarikan ini. Teller perlu mengambil tambahan kas dari brankas.']);
        }

        SavingsMockup::authorize($transaction, $user);

        return redirect(route('detailSavingsTransaction', $transaction['id']))
            ->with('success', 'Penarikan diotorisasi, diposting, dan jurnal terbentuk otomatis!');
    }

    public function reject(Request $request, string $id): RedirectResponse
    {
        $user = auth()->user();
        $transaction = SavingsMockup::findTransaction($user, (int) $id) ?? abort(404);

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:200',
        ]);

        $check = $this->authorizationCheck($transaction, $user);

        if (! $check['allowed']) {
            return back()->withErrors(['msg' => $check['message']]);
        }

        SavingsMockup::reject($transaction, $user, $data['rejection_reason']);

        return redirect(route('detailSavingsTransaction', $transaction['id']))->with('success', 'Penarikan ditolak!');
    }

    /**
     * @param  array<string, mixed>  $transaction
     * @return array{allowed: bool, message: string|null}
     */
    private function authorizationCheck(array $transaction, User $user): array
    {
        return match (true) {
            $transaction['status'] !== 'pending_authorization' => ['allowed' => false, 'message' => 'Transaksi ini tidak sedang menunggu otorisasi.'],
            $transaction['teller_id'] === $user->id => ['allowed' => false, 'message' => 'Teller tidak boleh mengotorisasi transaksinya sendiri.'],
            default => $this->authorityService->check($user, 'withdrawal', (string) $transaction['amount']),
        };
    }
}
