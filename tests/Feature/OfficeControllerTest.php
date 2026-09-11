<?php

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Office;
use Database\Seeders\AccountSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed([RoleSeeder::class, AccountSeeder::class]);
});

describe('index', function () {
    it('renders the office list for head office roles', function () {
        $admin = userWithRole('ADM');

        $this->actingAs($admin)
            ->get(route('office'))
            ->assertOk()
            ->assertSee($admin->office->name);
    });

    it('forbids branch roles', function () {
        $this->actingAs(userWithRole('KCB'))
            ->get(route('office'))
            ->assertForbidden();
    });
});

describe('store', function () {
    it('creates a branch office and records the audit trail', function () {
        $admin = userWithRole('ADM');

        $this->actingAs($admin)
            ->post(route('postOffice'), [
                'code' => '10',
                'name' => 'Cabang Bekasi',
                'type' => 'branch',
                'parent_id' => $admin->office_id,
                'address' => 'Jl. Ahmad Yani No. 1, Bekasi',
                'book_date' => '2026-09-11',
                'is_active' => '1',
            ])
            ->assertRedirect(route('office'))
            ->assertSessionHas('success', 'Kantor berhasil ditambahkan!');

        $office = Office::where('code', '10')->firstOrFail();

        $auditUserId = AuditLog::where('auditable_type', $office->getMorphClass())
            ->where('auditable_id', $office->id)
            ->where('event', 'created')
            ->value('user_id');

        expect($office->parent_id)->toBe($admin->office_id)
            ->and($office->is_active)->toBeTrue()
            ->and($auditUserId)->toBe($admin->id);
    });

    it('creates the RAK account pair for a new branch office', function () {
        $admin = userWithRole('ADM');
        $admin->office->update(['code' => '00', 'name' => 'Kantor Pusat']);

        $this->actingAs($admin)
            ->post(route('postOffice'), [
                'code' => '10',
                'name' => 'Cabang Bekasi',
                'type' => 'branch',
                'parent_id' => $admin->office_id,
                'book_date' => '2026-09-11',
            ])
            ->assertRedirect(route('office'));

        $office = Office::where('code', '10')->firstOrFail();

        expect(Account::where('code', '1.9.10')->firstOrFail()->only(['name', 'counterpart_office_id', 'is_head_office_only']))->toBe([
            'name' => 'RAK Cabang Bekasi',
            'counterpart_office_id' => $office->id,
            'is_head_office_only' => true,
        ])
            ->and(Account::where('code', '1.9.00')->value('name'))->toBe('RAK Kantor Pusat');
    });

    it('rejects a branch office without a parent office', function () {
        $this->actingAs(userWithRole('ADM'))
            ->post(route('postOffice'), [
                'code' => '10',
                'name' => 'Cabang Bekasi',
                'type' => 'branch',
                'book_date' => '2026-09-11',
            ])
            ->assertSessionHasErrors(['msg' => 'Kantor selain kantor pusat wajib memiliki kantor induk.']);

        expect(Office::where('code', '10')->exists())->toBeFalse();
    });

    it('rejects a second head office', function () {
        $this->actingAs(userWithRole('ADM'))
            ->post(route('postOffice'), [
                'code' => '99',
                'name' => 'Kantor Pusat Kedua',
                'type' => 'head_office',
                'book_date' => '2026-09-11',
            ])
            ->assertSessionHasErrors(['msg' => 'Kantor pusat sudah ada. Koperasi hanya memiliki satu kantor pusat.']);

        expect(Office::where('code', '99')->exists())->toBeFalse();
    });

    it('requires the mandatory fields', function () {
        $this->actingAs(userWithRole('ADM'))
            ->post(route('postOffice'), [])
            ->assertSessionHasErrors(['code', 'name', 'type', 'book_date']);
    });

    it('forbids view-only roles from creating offices', function () {
        $this->actingAs(userWithRole('MGR'))
            ->post(route('postOffice'), [])
            ->assertForbidden();
    });
});

describe('update', function () {
    it('updates the office without changing its code or book date', function () {
        $admin = userWithRole('ADM');
        $branch = Office::factory()->create([
            'parent_id' => $admin->office_id,
            'code' => '05',
            'book_date' => '2026-09-11',
        ]);

        $this->actingAs($admin)
            ->put(route('updateOffice', $branch->id), [
                'code' => '99',
                'book_date' => '2020-01-01',
                'name' => 'Cabang Cirebon Kota',
                'type' => 'branch',
                'parent_id' => $admin->office_id,
                'address' => 'Jl. Kartini No. 5, Cirebon',
            ])
            ->assertRedirect(route('office'))
            ->assertSessionHas('success', 'Kantor berhasil diperbarui!');

        $branch->refresh();

        expect($branch->name)->toBe('Cabang Cirebon Kota')
            ->and($branch->code)->toBe('05')
            ->and($branch->book_date->toDateString())->toBe('2026-09-11')
            ->and($branch->is_active)->toBeFalse();
    });

    it('rejects an office as its own parent', function () {
        $admin = userWithRole('ADM');
        $branch = Office::factory()->create(['parent_id' => $admin->office_id]);

        $this->actingAs($admin)
            ->put(route('updateOffice', $branch->id), [
                'name' => $branch->name,
                'type' => 'branch',
                'parent_id' => $branch->id,
            ])
            ->assertSessionHasErrors(['msg' => 'Kantor induk tidak boleh kantor itu sendiri.']);

        expect($branch->fresh()->parent_id)->toBe($admin->office_id);
    });
});
