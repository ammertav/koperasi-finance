<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'code' => fake()->unique()->numerify('9##'),
            'name' => 'Cabang '.fake()->city(),
            'type' => 'branch',
            'address' => fake()->address(),
            'book_date' => '2026-09-11',
            'is_active' => true,
        ];
    }

    public function headOffice(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Kantor Pusat',
            'type' => 'head_office',
        ]);
    }
}
