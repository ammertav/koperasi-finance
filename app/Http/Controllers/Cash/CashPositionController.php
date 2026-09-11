<?php

namespace App\Http\Controllers\Cash;

use App\Http\Controllers\Controller;
use App\Mockups\CashMockup;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Posisi kas dan bank seluruh kantor dari jurnal mockup (KAS-10).
 */
class CashPositionController extends Controller
{
    public function index(): View
    {
        $positions = CashMockup::positions(Auth::user());

        $totals = [
            'teller_cash' => array_sum(array_column($positions, 'teller_cash')),
            'vault_cash' => array_sum(array_column($positions, 'vault_cash')),
            'bank' => array_sum(array_column($positions, 'bank')),
            'total' => array_sum(array_column($positions, 'total')),
            'open_session_count' => array_sum(array_column($positions, 'open_session_count')),
        ];

        return view('cash.cashPosition', compact('positions', 'totals'));
    }
}
