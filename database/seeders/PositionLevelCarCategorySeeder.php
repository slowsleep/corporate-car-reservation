<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PositionLevelCarCategory;

class PositionLevelCarCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positionsCarCategories = [
            ['position_level' => 1, 'car_categories_id' => [1]],
            ['position_level' => 2, 'car_categories_id' => [1, 2]],
            ['position_level' => 3, 'car_categories_id' => [2, 3]],
            ['position_level' => 4, 'car_categories_id' => [3, 4]],
            ['position_level' => 5, 'car_categories_id' => [4, 5]],
        ];

        foreach ($positionsCarCategories as $item) {
            foreach ($item['car_categories_id'] as $categoryId) {
                PositionLevelCarCategory::insert(['position_level' => $item['position_level'], 'car_category_id' => $categoryId]);
            }
        }

    }
}
