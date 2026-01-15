<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Car extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'model',
        'car_category_id',
        'driver_id',
        'plate_number',
        'year'
    ];

    public function category()
    {
        return $this->belongsTo(CarCategory::class, 'car_category_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function trips()
    {
        return $this->hasMany(BusinessTrip::class);
    }

    public function scopeAvailable(Builder $query, ?Carbon $startTime = null)
    {
        $startTime = $startTime ?? now();

        return $query->whereNotExists(function ($subQuery) use ($startTime) {
            $subQuery->select(DB::raw(1))
                ->from('business_trips')
                ->whereColumn('business_trips.car_id', 'cars.id')
                ->whereIn('business_trips.status', ['planned', 'in_progress'])
                ->where(function ($q) use ($startTime) {
                    // Проверяем пересечение интервалов
                    $q->where(function ($innerQ) use ($startTime) {
                        // Если end_time не null, проверяем стандартное пересечение
                        $innerQ->whereNotNull('business_trips.end_time')
                            // ->where('business_trips.start_time', '<', $endTime)
                            ->where('business_trips.end_time', '>', $startTime);
                    })
                    ->orWhere(function ($innerQ) use ($startTime) {
                        // Если end_time null, проверяем что start_time в пределах интервала
                        // или что start_time был недавно (для in_progress)
                        $innerQ->whereNull('business_trips.end_time')
                            // ->where('business_trips.start_time', '<', $endTime)
                            ->where('business_trips.start_time', '>=', $startTime->copy()->subHours(24));
                            // И поездка началась не раньше чем 24 часа назад
                    });
                });
        });
    }
}
