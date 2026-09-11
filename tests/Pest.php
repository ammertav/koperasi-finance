<?php

use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\Office;
use App\Models\Role;
use App\Models\Scopes\OfficeScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Buat pengguna dengan satu peran hasil RoleSeeder. Kantor dibuat sesuai lokasi peran bila tidak diberikan.
 */
function userWithRole(string $roleCode, ?Office $office = null): User
{
    $role = Role::where('code', $roleCode)->firstOrFail();

    $office ??= Office::factory()->create([
        'type' => $role->location === 'head_office' ? 'head_office' : 'branch',
    ]);

    $user = User::factory()->for($office)->create();
    $user->roles()->attach($role);

    return $user;
}

/**
 * ID akun dari kode COA hasil AccountSeeder atau akun RAK yang dibuat otomatis.
 */
function accountId(string $code): int
{
    return Account::where('code', $code)->firstOrFail()->id;
}

/**
 * Baris jurnal sebagai [kode akun, debit, kredit] berurutan sesuai pembentukan.
 *
 * @return array<int, array{0: string, 1: string, 2: string}>
 */
function journalLines(Journal $journal): array
{
    return JournalDetail::withoutGlobalScope(OfficeScope::class)
        ->with('account')
        ->where('journal_id', $journal->id)
        ->orderBy('id')
        ->get()
        ->map(fn (JournalDetail $detail) => [$detail->account->code, $detail->debit, $detail->credit])
        ->all();
}
