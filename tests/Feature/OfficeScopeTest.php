<?php

use App\Models\Office;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('limits branch roles to data of their own office', function () {
    $ownBranch = Office::factory()->create();
    $otherBranch = Office::factory()->create();
    $branchHead = userWithRole('KCB', $ownBranch);
    User::factory()->for($ownBranch)->create();
    User::factory()->count(2)->for($otherBranch)->create();

    $this->actingAs($branchHead);

    expect(User::count())->toBe(2)
        ->and(User::pluck('office_id')->unique()->values()->all())->toBe([$ownBranch->id])
        ->and(Office::pluck('id')->all())->toBe([$ownBranch->id]);
});

it('lets head office roles see data of every office', function () {
    $headOffice = Office::factory()->headOffice()->create();
    $branch = Office::factory()->create();
    $manager = userWithRole('MGR', $headOffice);
    User::factory()->count(2)->for($branch)->create();

    $this->actingAs($manager);

    expect(User::count())->toBe(3)
        ->and(Office::count())->toBe(2);
});

it('does not filter queries when no user is logged in', function () {
    User::factory()->count(2)->create();

    expect(User::count())->toBe(2);
});
