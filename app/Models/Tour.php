<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'food_type',
        'time',
        'number_of_days',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tourItems()
    {
        return $this->hasMany(TourItem::class);
    }
}
