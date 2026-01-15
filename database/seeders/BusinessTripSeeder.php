<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessTrip;

class BusinessTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BusinessTrip::factory()->planned()->create();
        BusinessTrip::factory(5)->inProgress()->create();
        BusinessTrip::factory()->completed()->create();
        BusinessTrip::factory()->cancelled()->create();
        BusinessTrip::factory()->create(); // рандомная поездка
    }
}
