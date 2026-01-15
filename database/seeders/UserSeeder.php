<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Position;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeRoleId = Role::where('name', 'employee')->first()->id;
        $positions = Position::all()->toArray();

        foreach($positions as $position) {
            $name = explode(' ', strtolower($position['name']))[0];
            User::factory()->create([
                'name' => $name,
                'role_id' => $employeeRoleId,
                'position_id' => $position['id'],
                'email' => $name . '@example.com',
                'password' => '1234567890',
            ]);
        }

        User::factory(15)->driver()->create();
    }
}
