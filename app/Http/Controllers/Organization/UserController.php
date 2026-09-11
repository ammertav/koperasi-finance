<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['office', 'roles'])->orderBy('office_id')->orderBy('name')->get();

        $offices = Office::where('is_active', true)->orderBy('code')->get();

        $roles = Role::orderBy('id')->get();

        return view('organization.user', compact('users', 'offices', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'office_id' => 'required|exists:offices,id',
            'password' => 'required|string|min:8',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $roleError = $this->roleLocationError(Office::findOrFail($data['office_id']), $data['role_ids']);

        if ($roleError) {
            return back()->withErrors(['msg' => $roleError])->withInput($request->except('password'));
        }

        $user = User::create([
            'office_id' => $data['office_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => $request->has('is_active'),
        ]);

        $user->roles()->sync($data['role_ids']);

        return redirect(route('user'))->with('success', 'Pengguna berhasil ditambahkan!');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'office_id' => 'required|exists:offices,id',
            'password' => 'nullable|string|min:8',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $isActive = $request->has('is_active');

        if ($user->id === auth()->id() && ! $isActive) {
            return back()->withErrors(['msg' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        $roleError = $this->roleLocationError(Office::findOrFail($data['office_id']), $data['role_ids']);

        if ($roleError) {
            return back()->withErrors(['msg' => $roleError])->withInput($request->except('password'));
        }

        $attributes = [
            'office_id' => $data['office_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $isActive,
        ];

        // Kata sandi diisi berarti reset kata sandi oleh admin (ORG-02).
        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        $user->update($attributes);

        $user->roles()->sync($data['role_ids']);

        return redirect(route('user'))->with('success', 'Pengguna berhasil diperbarui!');
    }

    /**
     * Peran pusat hanya untuk pengguna kantor pusat, peran cabang hanya untuk kantor di bawahnya.
     *
     * @param  array<int, int|string>  $roleIds
     */
    private function roleLocationError(Office $office, array $roleIds): ?string
    {
        $location = $office->type === 'head_office' ? 'head_office' : 'branch';

        $invalidRoles = Role::whereIn('id', $roleIds)->where('location', '!=', $location)->pluck('name');

        if ($invalidRoles->isEmpty()) {
            return null;
        }

        return 'Peran '.$invalidRoles->implode(', ').' tidak dapat dipakai di kantor '.strtolower(Role::LOCATIONS[$location]).'.';
    }
}
