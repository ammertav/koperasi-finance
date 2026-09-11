<?php

namespace Database\Seeders;

use App\Models\AuthorityLimit;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AuthorityLimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('demo.authority_limits') as $roleCode => $limits) {
            $role = Role::where('code', $roleCode)->firstOrFail();

            foreach ($limits as $transactionType => $maxAmount) {
                AuthorityLimit::updateOrCreate(
                    ['role_id' => $role->id, 'transaction_type' => $transactionType],
                    ['max_amount' => $maxAmount ?? '0.00', 'is_unlimited' => $maxAmount === null]
                );
            }
        }
    }
}
