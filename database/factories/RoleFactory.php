<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('X???')),
            'name' => fake()->jobTitle(),
            'location' => 'branch',
            'data_scope' => 'own_office',
        ];
    }

    public function headOffice(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => 'head_office',
            'data_scope' => 'all_offices',
        ]);
    }
}
