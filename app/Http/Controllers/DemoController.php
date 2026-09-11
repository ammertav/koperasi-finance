<?php

namespace App\Http\Controllers;

use App\Models\Scopes\OfficeScope;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    /**
     * Pengalih peran demo "Masuk sebagai..." tanpa logout (BRIEF). Hanya aktif jika APP_DEMO=true.
     */
    public function switchUser(Request $request): RedirectResponse
    {
        abort_unless(config('demo.enabled'), 404);

        $data = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::withoutGlobalScope(OfficeScope::class)
            ->where('id', $data['user_id'])
            ->where('is_active', true)
            ->firstOrFail();

        Auth::login($user);

        $request->session()->regenerate();

        return redirect(route('dashboard'))->with('toast_success', "Masuk sebagai {$user->name}");
    }
}
