<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteSightseeing extends Model
{
    protected $table = 'user_favorite_sightseeings';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'history_sightseeing_id',
        'user_id'
    ];

    /**Tạo lúc
     * Get the history item that owns the food.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function historySightseeing()
    {
        return $this->belongsTo(HistorySightseeing::class, 'history_sightseeing_id');
    }
}
