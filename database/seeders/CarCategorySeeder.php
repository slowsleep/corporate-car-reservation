<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CarCategory;

class CarCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CarCategory::insert([
            ['name' => 'Economy', 'level' => 1],
            ['name' => 'Comfort', 'level' => 2],
            ['name' => 'Business', 'level' => 3],
            ['name' => 'Premium', 'level' => 4],
            ['name' => 'Luxury', 'level' => 5],
        ]);
    }
}
