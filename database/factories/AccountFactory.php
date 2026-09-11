<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
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
            'counterpart_office_id' => null,
            'code' => fake()->unique()->numerify('9.9.###'),
            'name' => 'Akun '.fake()->word(),
            'type' => 'asset',
            'normal_balance' => 'debit',
            'level' => 3,
            'is_postable' => true,
            'is_head_office_only' => false,
            'is_active' => true,
        ];
    }

    public function header(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 2,
            'is_postable' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function headOfficeOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_head_office_only' => true,
        ]);
    }
}
