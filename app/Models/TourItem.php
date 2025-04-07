<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourItem extends Model
{
    protected $fillable = [
        'tour_id',
        'name',
        'address',
        'description',
        'latitude',
        'longitude',
        'suggested_time',
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
