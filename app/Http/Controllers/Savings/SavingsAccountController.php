<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Mockups\MemberMockup;
use App\Mockups\SavingsMockup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Rekening simpanan dan mutasinya versi prototype tampilan (SIM-02, SIM-15).
 */
class SavingsAccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $productCode = $request->query('product');
        $productCode = array_key_exists((string) $productCode, SavingsMockup::PRODUCTS) ? $productCode : null;

        $allAccounts = collect(SavingsMockup::accountsForUser($user));
        $accounts = $productCode ? $allAccounts->where('product_code', $productCode)->values()->all() : $allAccounts->all();

        $summary = collect(SavingsMockup::PRODUCTS)
            ->map(fn (string $name, string $code) => [
                'name' => $name,
                'account_count' => $allAccounts->where('product_code', $code)->count(),
                'total_balance' => $allAccounts->where('product_code', $code)->sum('balance'),
            ])
            ->all();

        $products = SavingsMockup::PRODUCTS;
        $showOffice = $user->canAccessAllOffices();

        return view('savings.savingsAccount', compact('accounts', 'summary', 'products', 'productCode', 'showOffice'));
    }

    public function show(string $id): View
    {
        [$account, $member, $mutations, $summary] = $this->statement($id);

        return view('savings.savingsAccountShow', compact('account', 'member', 'mutations', 'summary'));
    }

    public function print(string $id): View
    {
        [$account, $member, $mutations, $summary] = $this->statement($id);

        return view('savings.savingsAccountPrint', compact('account', 'member', 'mutations', 'summary'));
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: array<int, array<string, mixed>>, 3: array<string, mixed>}
     */
    private function statement(string $number): array
    {
        $user = Auth::user();
        $account = SavingsMockup::findAccount($user, $number) ?? abort(404);
        $member = MemberMockup::find($user, $account['member_id']) ?? abort(404);
        $mutations = SavingsMockup::mutations($account);

        $summary = [
            'opening_balance' => $mutations[0]['balance'],
            'total_credit' => array_sum(array_column($mutations, 'credit')),
            'total_debit' => array_sum(array_column($mutations, 'debit')),
            'period_start' => $mutations[0]['date'],
            'available_balance' => $account['product_code'] === 'SS' ? max(0, SavingsMockup::availableBalance($account)) : 0,
        ];

        return [$account, $member, $mutations, $summary];
    }
}
