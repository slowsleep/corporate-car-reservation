<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PositionSeeder::class,
            CarCategorySeeder::class,
        ]);

        $this->call([
            PositionLevelCarCategorySeeder::class,
        ]);

        $this->call([
            UserSeeder::class,
        ]);

        $this->call([
            CarSeeder::class,
        ]);

        $this->call([
            BusinessTripSeeder::class,
        ]);
    }
}
