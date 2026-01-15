<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Car;
use App\Models\Position;
use App\Models\PositionLevelCarCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    public function available(Request $request)
    {
        try {
            // Валидация входных параметров
            $validator = Validator::make($request->all(), [
                'start_date' => 'nullable|date',
                'model' => 'nullable|string|max:100',
                'category' => 'nullable|integer|exists:car_categories,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Проверка что пользователь авторизован
            if (!$user) {
                return response()->json([
                    'error' => true,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Проверка что у пользователя есть position_id
            if (!$user->position_id) {
                return response()->json([
                    'error' => true,
                    'message' => 'User has no position assigned'
                ], 403);
            }

            // Получаем уровень должности
            $position = Position::find($user->position_id);
            if (!$position) {
                return response()->json([
                    'error' => true,
                    'message' => 'Position not found'
                ], 403);
            }

            $userPositionLevel = $position->level;

            // Проверка уровня должности
            if ($userPositionLevel == 0) {
                return response()->json([
                    'error' => true,
                    'message' => "You don't have enough level position"
                ], 403);
            }

            // Получаем доступные категории
            $availableCategories = PositionLevelCarCategory::where('position_level', $userPositionLevel)
                ->pluck('car_category_id')
                ->toArray();

            if (empty($availableCategories)) {
                return response()->json([
                    'error' => true,
                    'message' => 'No car categories available for your position level'
                ], 403);
            }

            // Проверка категории (если указана)
            $category = $request->query('category');
            if ($category && !in_array($category, $availableCategories)) {
                return response()->json([
                    'error' => true,
                    'message' => 'This car category is unavailable for you!'
                ], 403);
            }

            // Обработка дат
            $startDate = $request->query('start_date');

            if ($startDate) {
                $startDate = Carbon::parse($startDate);

                $cars = Car::whereIn('car_category_id', $availableCategories)
                    ->available($startDate);
            } else {
                $cars = Car::whereIn('car_category_id', $availableCategories)
                    ->available();
            }

            // Применяем фильтры
            $model = $request->query('model');
            if ($model) {
                $cars = $cars->where('model', 'like', '%' . $model . '%');
            }

            if ($category) {
                $cars = $cars->where('car_category_id', $category);
            }

            // Получаем данные с отношениями
            $cars = $cars->with(['category', 'driver', 'driver.position'])
                ->get()
                ->map(function ($car) {
                    return [
                        'id' => $car->id,
                        'model' => $car->model,
                        'plate_number' => $car->plate_number,
                        'year' => $car->year,
                        'category' => $car->category ? [
                            'id' => $car->category->id,
                            'name' => $car->category->name,
                            'comfort_level' => $car->category->comfort_level,
                        ] : null,
                        'driver' => $car->driver ? [
                            'id' => $car->driver->id,
                            'name' => $car->driver->name,
                            'email' => $car->driver->email,
                        ] : null,
                        'created_at' => $car->created_at,
                        'updated_at' => $car->updated_at,
                    ];
                })
                ;

            return response()->json([
                'success' => true,
                'data' => $cars,
                'meta' => [
                    'total' => $cars->count(),
                    'position_level' => $userPositionLevel,
                    'available_categories' => $availableCategories,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Internal server error',
            ], 500);
        }
    }
}
