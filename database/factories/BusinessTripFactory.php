<?php

namespace Database\Factories;

use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Role;
use App\Models\Car;
use App\Models\PositionLevelCarCategory;
use App\Models\Position;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessTrip>
 */
class BusinessTripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userEmployeeRoleId = Role::where('name', 'employee')->first()->id;

        $employee = User::where('role_id', $userEmployeeRoleId)
            ->whereNot('position_id', 1)
            ->whereDoesntHave('businessTrips', function ($query) {
                $query->whereDate('start_time', today()) // поездки, начинающиеся сегодня
                    ->whereIn('status', ['planned', 'in_progress']); // активные статусы
                })
            ->inRandomOrder()
            ->first();

        $positionLevel = Position::where('id', $employee->position_id)->first()->level;
        $carCategories = PositionLevelCarCategory::where('position_level',  $positionLevel)->pluck('car_category_id')->toArray();
        $carId = Car::whereIn('car_category_id', $carCategories)->available()->inRandomOrder()->first()->id;

        $status = fake()->randomElement(['planned', 'in_progress', 'completed', 'cancelled']);
        $startTime = null;
        $endTime = null;
        $startAddress = fake()->address();
        $endAddress = fake()->address();


        if (in_array($status, ['planned', 'in_progress', 'completed'])) {
            $startTime = fake()->dateTimeBetween('-6 hours');
            $startTime = Carbon::instance($startTime);
        }

        if (in_array($status, ['completed'])) {
            $durationInMinutes = rand(5, 90);
            $endTime = $startTime->copy()->addMinutes($durationInMinutes);
        }

        return [
            'employee_id' => $employee->id,
            'car_id' => $carId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_address' => $startAddress,
            'end_address' => $endAddress,
            'status' => $status,
        ];
    }

    public function planned(): Factory
    {
        return $this->state(function (array $attributes) {
            $startTime = Carbon::now()->addHours(rand(1, 72));

            return [
                'status' => 'planned',
                'start_time' => $startTime,
                'end_time' => null,
            ];
        });
    }

    public function inProgress(): Factory
    {
        return $this->state(function (array $attributes) {
            $startTime = Carbon::now()->subMinutes(rand(1, 30));

            return [
                'status' => 'in_progress',
                'start_time' => $startTime,
                'end_time' => null,
            ];
        });
    }

    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            $startTime = Carbon::now()->subHours(rand(1, 48));
            $duration = rand(5, 90);
            $startAddress = fake()->address();
            $endAddress = fake()->address();

            return [
                'status' => 'completed',
                'start_time' => $startTime,
                'end_time' => $startTime->copy()->addMinutes($duration),
                'start_address' => $startAddress,
                'end_address' => $endAddress,
            ];
        });
    }

    public function cancelled(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'start_time' => null,
                'end_time' => null,
            ];
        });
    }
}
