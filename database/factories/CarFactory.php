<?php

namespace Database\Factories;

use App\Models\CarCategory;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $driverRoleId = Role::where('name', 'driver')->first()->id;

        return [
            'model' => fake()->title,
            'car_category_id' => CarCategory::inRandomOrder()->first()->id,
            'driver_id' => User::where('role_id', $driverRoleId)->inRandomOrder()->first()->id,
            'plate_number' => fake()->numberBetween(2, 6),
            'year' => (int) fake()->year(),
        ];
    }
}
