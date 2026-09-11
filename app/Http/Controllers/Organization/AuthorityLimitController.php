<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\AuthorityLimit;
use App\Models\Role;
use Illuminate\View\View;

class AuthorityLimitController extends Controller
{
    public function index(): View
    {
        $roles = Role::whereHas('authorityLimits')->with('authorityLimits')->orderBy('id')->get();

        $transactionTypes = AuthorityLimit::TRANSACTION_TYPES;

        return view('organization.authorityLimit', compact('roles', 'transactionTypes'));
    }
}
