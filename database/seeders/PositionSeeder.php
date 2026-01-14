<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Должность - уровень доступа (чем выше число, тем больше доступ)
        Position::insert([
            ['name' => 'Intern', 'level' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Junior Developer', 'level' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Middle Developer', 'level' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Senior Developer', 'level' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Team Lead', 'level' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Project Manager', 'level' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Department Head', 'level' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Director', 'level' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CEO', 'level' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
