<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookDate = now()->toDateString();
        $headOfficeId = null;

        foreach (config('demo.offices') as $code => $office) {
            $isHeadOffice = $code === '00';

            $model = Office::updateOrCreate(['code' => $code], [
                'parent_id' => $isHeadOffice ? null : $headOfficeId,
                'name' => $office['name'],
                'type' => $isHeadOffice ? 'head_office' : 'branch',
                'address' => $office['address'],
                'book_date' => $bookDate,
                'is_active' => true,
            ]);

            if ($isHeadOffice) {
                $headOfficeId = $model->id;
            }
        }
    }
}
