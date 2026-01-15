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
        $randDriverWithoutCar = User::where('role_id', Role::where('name', 'driver')->first()->id)
            ->whereDoesntHave('car')
            ->get()->first()->id;

        return [
            'model' => fake()->word(),
            'car_category_id' => CarCategory::inRandomOrder()->first()->id,
            'driver_id' => $randDriverWithoutCar,
            'plate_number' => $this->generatePlateNumber(),
            'year' => (int) fake()->year(),
            'color' => fake()->colorName(),
        ];
    }

    private function generatePlateNumber(): string
    {
        $letters = ['А', 'В', 'Е', 'К', 'М', 'Н', 'О', 'Р', 'С', 'Т', 'У', 'Х'];
        return $letters[array_rand($letters)] .
                rand(100, 999) .
                $letters[array_rand($letters)] .
                $letters[array_rand($letters)];
    }
}
