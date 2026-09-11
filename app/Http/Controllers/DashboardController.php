<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user()->load(['office', 'roles.permissions']);

        $totalOffices = Office::count();
        $activeOffices = Office::where('is_active', true)->count();
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        $permissions = $user->roles->pluck('permissions')->flatten();

        $accessByModule = collect(RolePermission::MODULES)->map(fn (string $label, string $module) => [
            'label' => $label,
            'access' => $permissions->where('module', $module)->pluck('access')->unique()->values()->all(),
        ]);

        return view('dashboard', compact('user', 'totalOffices', 'activeOffices', 'totalUsers', 'activeUsers', 'accessByModule'));
    }
}
