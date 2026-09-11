<?php

use App\Services\Organization\AuthorityService;
use Database\Seeders\AuthorityLimitSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, AuthorityLimitSeeder::class]);
});

it('checks the amount against the authority matrix', function (string $roleCode, string $amount, array $expected) {
    $user = userWithRole($roleCode);

    $result = app(AuthorityService::class)->check($user, 'reversal', $amount);

    expect($result)->toBe($expected);
})->with([
    'branch head at the limit' => ['KCB', '10000000', ['allowed' => true, 'message' => null]],
    'branch head above the limit' => ['KCB', '10000001', [
        'allowed' => false,
        'message' => 'Nominal Rp 10.000.001 melebihi batas wewenang pembalikan transaksi Anda (Rp 10.000.000).',
    ]],
    'accounting head without a limit' => ['KAK', '5000000000', ['allowed' => true, 'message' => null]],
    'teller without authority' => ['TLR', '1000', [
        'allowed' => false,
        'message' => 'Peran Anda tidak memiliki wewenang pembalikan transaksi.',
    ]],
]);
