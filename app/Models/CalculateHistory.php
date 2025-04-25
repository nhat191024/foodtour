<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalculateHistory extends Model
{
    protected $fillable = [
        'trip_name',
        'total_per_person',
        'total',
        'user_id',
        'tour_item_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tourItem()
    {
        return $this->belongsTo(TourItem::class, 'tour_item_id');
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function foods()
    {
        return $this->hasMany(Food::class);
    }
}
