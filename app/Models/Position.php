<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'level'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function availableCategories()
    {
        return $this->belongsToMany(CarCategory::class, 'position_car_category');
    }
}
