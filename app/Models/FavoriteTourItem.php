<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteTourItem extends Model
{
    protected $fillable = [
        'user_id',
        'tour_item_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tourItems()
    {
        return $this->belongsTo(TourItem::class, 'tour_item_id');
    }

}
