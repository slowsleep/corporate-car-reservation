<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Car;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cars = [
            ['models' => ['Kia Rio', 'Hyundai Solaris', 'Volkswagen Polo'], 'car_category_id' => 1],
            ['models' => ['Toyota Camry', 'Skoda Octavia', 'Volkswagen Passat'], 'car_category_id' => 2],
            ['models' => ['Mercedes-Benz E-Class', 'BMW 5 Series', 'Audi A6'], 'car_category_id' => 3],
            ['models' => ['Mercedes-Benz S-Class', 'BMW 7 Series', 'Audi A8'], 'car_category_id' => 4],
            ['models' => ['Porsche Panamera', 'Mercedes-Maybach', 'Bentley'], 'car_category_id' => 5]
        ];

        foreach ($cars as $car) {
            foreach ($car['models'] as $carModel) {
                Car::factory()->create(['model' => $carModel, 'car_category_id' => $car['car_category_id']]);
            }
        }
    }
}
