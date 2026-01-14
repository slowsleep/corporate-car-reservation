<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionLevelCarCategory extends Model
{

    protected $table = 'position_level_car_category';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'position_level',
        'car_category_id'
    ];
}
