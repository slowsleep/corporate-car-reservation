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

    public function scopeAvailable(Builder $query, ?Carbon $startTime = null, ?Carbon $endTime = null)
    {
        $startTime = $startTime ?? now();
        $endTime = $endTime ?? now()->addHours(2);

        return $query->whereNotExists(function ($subQuery) use ($startTime, $endTime) {
            $subQuery->select(DB::raw(1))
                ->from('business_trips')
                ->whereColumn('business_trips.car_id', 'cars.id')
                ->where(function ($q) use ($startTime, $endTime) {
                    // Проверяем пересечение интервалов
                    $q->where(function ($innerQ) use ($startTime, $endTime) {
                        // Поездка пересекается с запрашиваемым интервалом
                        $innerQ->where('business_trips.start_time', '<', $endTime)
                            ->where('business_trips.end_time', '>', $startTime);
                    })
                    // И поездка активна (не отменена и не завершена)
                    ->whereIn('business_trips.status', ['planned', 'in_progress']);
                });
        });
    }

}
