<?php

use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RoleSeeder::class);
});

it('logs in every role and renders the dashboard', function (string $roleCode) {
    $user = userWithRole($roleCode);

    $this->post(route('signin'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee($user->name);
})->with(['ADM', 'MGR', 'KAK', 'SAK', 'KKR', 'AUD', 'PGR', 'KCB', 'CS', 'TLR', 'ANK', 'ADC']);

it('rejects an inactive user', function () {
    $user = userWithRole('TLR');
    $user->update(['is_active' => false]);

    $this->post(route('signin'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah, atau akun tidak aktif.']);

    $this->assertGuest();
});

it('rejects a wrong password', function () {
    $user = userWithRole('CS');

    $this->post(route('signin'), ['email' => $user->email, 'password' => 'sandi-salah'])
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah, atau akun tidak aktif.']);

    $this->assertGuest();
});

it('redirects guests from the dashboard to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('logs out and redirects to the login page', function () {
    $this->actingAs(userWithRole('CS'))
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
