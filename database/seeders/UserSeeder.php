<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Kata sandi setiap pengguna demo per email. Setelah diubah, jalankan
     * `php artisan db:seed --class=UserSeeder` untuk memperbarui kata sandi di database.
     *
     * @var array<string, string>
     */
    private const PASSWORDS = [
        // Kantor Pusat (00)
        'adm@ksp.test' => 'adm-ksp',
        'mgr@ksp.test' => 'mgr-ksp',
        'kak@ksp.test' => 'kak-ksp',
        'sak@ksp.test' => 'sak-ksp',
        'kkr@ksp.test' => 'kkr-ksp',
        'aud@ksp.test' => 'aud-ksp',
        'pgr@ksp.test' => 'pgr-ksp',

        // Cabang Bandung (01)
        'kcb01@ksp.test' => 'kcb01-ksp',
        'cs01@ksp.test' => 'cs01-ksp',
        'tlr01@ksp.test' => 'tlr01-ksp',
        'ank01@ksp.test' => 'ank01-ksp',
        'adc01@ksp.test' => 'adc01-ksp',

        // Cabang Cimahi (02)
        'kcb02@ksp.test' => 'kcb02-ksp',
        'cs02@ksp.test' => 'cs02-ksp',
        'tlr02@ksp.test' => 'tlr02-ksp',
        'ank02@ksp.test' => 'ank02-ksp',
        'adc02@ksp.test' => 'adc02-ksp',

        // Cabang Garut (03)
        'kcb03@ksp.test' => 'kcb03-ksp',
        'cs03@ksp.test' => 'cs03-ksp',
        'tlr03@ksp.test' => 'tlr03-ksp',
        'ank03@ksp.test' => 'ank03-ksp',
        'adc03@ksp.test' => 'adc03-ksp',

        // Cabang Tasikmalaya (04)
        'kcb04@ksp.test' => 'kcb04-ksp',
        'cs04@ksp.test' => 'cs04-ksp',
        'tlr04@ksp.test' => 'tlr04-ksp',
        'ank04@ksp.test' => 'ank04-ksp',
        'adc04@ksp.test' => 'adc04-ksp',

        // Cabang Cirebon (05)
        'kcb05@ksp.test' => 'kcb05-ksp',
        'cs05@ksp.test' => 'cs05-ksp',
        'tlr05@ksp.test' => 'tlr05-ksp',
        'ank05@ksp.test' => 'ank05-ksp',
        'adc05@ksp.test' => 'adc05-ksp',

        // Cabang Sumedang (06)
        'kcb06@ksp.test' => 'kcb06-ksp',
        'cs06@ksp.test' => 'cs06-ksp',
        'tlr06@ksp.test' => 'tlr06-ksp',
        'ank06@ksp.test' => 'ank06-ksp',
        'adc06@ksp.test' => 'adc06-ksp',

        // Cabang Subang (07)
        'kcb07@ksp.test' => 'kcb07-ksp',
        'cs07@ksp.test' => 'cs07-ksp',
        'tlr07@ksp.test' => 'tlr07-ksp',
        'ank07@ksp.test' => 'ank07-ksp',
        'adc07@ksp.test' => 'adc07-ksp',

        // Cabang Purwakarta (08)
        'kcb08@ksp.test' => 'kcb08-ksp',
        'cs08@ksp.test' => 'cs08-ksp',
        'tlr08@ksp.test' => 'tlr08-ksp',
        'ank08@ksp.test' => 'ank08-ksp',
        'adc08@ksp.test' => 'adc08-ksp',

        // Cabang Sukabumi (09)
        'kcb09@ksp.test' => 'kcb09-ksp',
        'cs09@ksp.test' => 'cs09-ksp',
        'tlr09@ksp.test' => 'tlr09-ksp',
        'ank09@ksp.test' => 'ank09-ksp',
        'adc09@ksp.test' => 'adc09-ksp',
    ];

    /**
     * Satu pengguna per peran pusat, dan satu pengguna per peran cabang di setiap cabang (BRIEF data demo).
     * Email: {kode peran}@ksp.test untuk pusat, {kode peran}{kode kantor}@ksp.test untuk cabang.
     */
    public function run(): void
    {
        $faker = FakerFactory::create('id_ID');
        $faker->seed(2026);

        $headOffice = Office::where('code', '00')->firstOrFail();

        foreach (Role::where('location', 'head_office')->orderBy('id')->get() as $role) {
            $this->createUser($headOffice, $role, strtolower($role->code).'@ksp.test', $faker->name());
        }

        $branchRoles = Role::where('location', 'branch')->orderBy('id')->get();

        foreach (Office::where('type', 'branch')->orderBy('code')->get() as $office) {
            foreach ($branchRoles as $role) {
                $this->createUser($office, $role, strtolower($role->code).$office->code.'@ksp.test', $faker->name());
            }
        }
    }

    /**
     * Membuat atau memperbarui pengguna demo, termasuk mengembalikan kata sandinya ke nilai di PASSWORDS.
     */
    private function createUser(Office $office, Role $role, string $email, string $name): void
    {
        $password = self::PASSWORDS[$email] ?? throw new RuntimeException("Kata sandi untuk {$email} belum diisi di UserSeeder.");

        $user = User::updateOrCreate(['email' => $email], [
            'office_id' => $office->id,
            'name' => $name,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
