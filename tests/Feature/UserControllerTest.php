<?php

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RoleSeeder::class);
});

describe('index', function () {
    it('renders the user list for the system admin', function () {
        $admin = userWithRole('ADM');

        $this->actingAs($admin)
            ->get(route('user'))
            ->assertOk()
            ->assertSee($admin->email);
    });

    it('forbids branch roles', function () {
        $this->actingAs(userWithRole('TLR'))
            ->get(route('user'))
            ->assertForbidden();
    });
});

describe('store', function () {
    it('creates a branch user with branch roles', function () {
        $admin = userWithRole('ADM');
        $branch = Office::factory()->create(['parent_id' => $admin->office_id]);

        $this->actingAs($admin)
            ->post(route('postUser'), [
                'name' => 'Siti Aminah',
                'email' => 'siti@ksp.test',
                'office_id' => $branch->id,
                'password' => 'rahasia123',
                'role_ids' => Role::whereIn('code', ['CS', 'TLR'])->pluck('id')->all(),
                'is_active' => '1',
            ])
            ->assertRedirect(route('user'))
            ->assertSessionHas('success', 'Pengguna berhasil ditambahkan!');

        $user = User::where('email', 'siti@ksp.test')->firstOrFail();

        expect($user->office_id)->toBe($branch->id)
            ->and($user->roles->pluck('code')->sort()->values()->all())->toBe(['CS', 'TLR'])
            ->and(Hash::check('rahasia123', $user->password))->toBeTrue();
    });

    it('rejects a branch role for a head office user', function () {
        $admin = userWithRole('ADM');

        $this->actingAs($admin)
            ->post(route('postUser'), [
                'name' => 'Budi Santoso',
                'email' => 'budi@ksp.test',
                'office_id' => $admin->office_id,
                'password' => 'rahasia123',
                'role_ids' => [Role::where('code', 'TLR')->value('id')],
            ])
            ->assertSessionHasErrors(['msg' => 'Peran Teller tidak dapat dipakai di kantor pusat.']);

        expect(User::where('email', 'budi@ksp.test')->exists())->toBeFalse();
    });

    it('forbids view-only roles from creating users', function () {
        $this->actingAs(userWithRole('AUD'))
            ->post(route('postUser'), [])
            ->assertForbidden();
    });
});

describe('update', function () {
    it('keeps the current password when the password field is empty', function () {
        $admin = userWithRole('ADM');
        $staff = userWithRole('SAK', $admin->office);
        $currentPasswordHash = $staff->password;

        $this->actingAs($admin)
            ->put(route('updateUser', $staff->id), [
                'name' => 'Nama Baru',
                'email' => $staff->email,
                'office_id' => $staff->office_id,
                'password' => '',
                'role_ids' => [Role::where('code', 'SAK')->value('id')],
                'is_active' => '1',
            ])
            ->assertRedirect(route('user'));

        $staff->refresh();

        expect($staff->name)->toBe('Nama Baru')
            ->and($staff->password)->toBe($currentPasswordHash);
    });

    it('resets the password when a new one is given', function () {
        $admin = userWithRole('ADM');
        $staff = userWithRole('SAK', $admin->office);

        $this->actingAs($admin)
            ->put(route('updateUser', $staff->id), [
                'name' => $staff->name,
                'email' => $staff->email,
                'office_id' => $staff->office_id,
                'password' => 'sandibaru123',
                'role_ids' => [Role::where('code', 'SAK')->value('id')],
                'is_active' => '1',
            ])
            ->assertRedirect(route('user'));

        expect(Hash::check('sandibaru123', $staff->fresh()->password))->toBeTrue();
    });

    it('prevents admins from deactivating their own account', function () {
        $admin = userWithRole('ADM');

        $this->actingAs($admin)
            ->put(route('updateUser', $admin->id), [
                'name' => $admin->name,
                'email' => $admin->email,
                'office_id' => $admin->office_id,
                'role_ids' => [Role::where('code', 'ADM')->value('id')],
            ])
            ->assertSessionHasErrors(['msg' => 'Anda tidak dapat menonaktifkan akun sendiri.']);

        expect($admin->fresh()->is_active)->toBeTrue();
    });
});
