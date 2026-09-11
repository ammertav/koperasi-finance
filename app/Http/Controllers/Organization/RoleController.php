<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('id')->get();

        $modules = RolePermission::MODULES;

        return view('organization.role', compact('roles', 'modules'));
    }
}
