<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Satu pengguna per peran pusat, dan satu pengguna per peran cabang di setiap cabang (BRIEF data demo).
     * Email: {kode peran}@ksp.test untuk pusat, {kode peran}{kode kantor}@ksp.test untuk cabang.
     */
    public function run(): void
    {
        $faker = FakerFactory::create('id_ID');
        $faker->seed(2026);

        $password = Hash::make(config('demo.password'));
        $headOffice = Office::where('code', '00')->firstOrFail();

        foreach (Role::where('location', 'head_office')->orderBy('id')->get() as $role) {
            $this->createUser($headOffice, $role, strtolower($role->code).'@ksp.test', $faker->name(), $password);
        }

        $branchRoles = Role::where('location', 'branch')->orderBy('id')->get();

        foreach (Office::where('type', 'branch')->orderBy('code')->get() as $office) {
            foreach ($branchRoles as $role) {
                $this->createUser($office, $role, strtolower($role->code).$office->code.'@ksp.test', $faker->name(), $password);
            }
        }
    }

    private function createUser(Office $office, Role $role, string $email, string $name, string $password): void
    {
        $user = User::firstOrCreate(['email' => $email], [
            'office_id' => $office->id,
            'name' => $name,
            'password' => $password,
            'is_active' => true,
        ]);

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
