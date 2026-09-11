<?php

use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RoleSeeder::class);
});

it('returns 404 when demo mode is disabled', function () {
    config(['demo.enabled' => false]);
    $manager = userWithRole('MGR');
    $teller = userWithRole('TLR');

    $this->actingAs($manager)
        ->post(route('switchUser'), ['user_id' => $teller->id])
        ->assertNotFound();

    $this->assertAuthenticatedAs($manager);
});

it('switches to an active user of another office', function () {
    config(['demo.enabled' => true]);
    $teller = userWithRole('TLR');
    $branchHead = userWithRole('KCB');

    $this->actingAs($teller)
        ->post(route('switchUser'), ['user_id' => $branchHead->id])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($branchHead);
});

it('does not switch to an inactive user', function () {
    config(['demo.enabled' => true]);
    $teller = userWithRole('TLR');
    $inactiveUser = userWithRole('CS');
    $inactiveUser->update(['is_active' => false]);

    $this->actingAs($teller)
        ->post(route('switchUser'), ['user_id' => $inactiveUser->id])
        ->assertNotFound();

    $this->assertAuthenticatedAs($teller);
});
