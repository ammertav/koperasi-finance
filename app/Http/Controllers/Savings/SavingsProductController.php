<?php

namespace App\Http\Controllers\Savings;

use App\Http\Controllers\Controller;
use App\Mockups\SavingsMockup;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Produk simpanan versi prototype tampilan: konfigurasi hanya dapat dilihat (SIM-01).
 */
class SavingsProductController extends Controller
{
    public function index(): View
    {
        $accounts = collect(SavingsMockup::accountsForUser(Auth::user()));

        $products = collect(SavingsMockup::products())
            ->map(fn (array $product) => [
                ...$product,
                'account_count' => $accounts->where('product_code', $product['code'])->count(),
                'total_balance' => $accounts->where('product_code', $product['code'])->sum('balance'),
            ])
            ->all();

        return view('savings.savingsProduct', compact('products'));
    }
}
