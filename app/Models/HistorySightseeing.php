<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class HistorySightseeing extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'history_item_id',
        'name',
        'description',
        'address',
        'food_type',
        'note',
        'latitude',
        'longitude',
    ];

    /**
     * Get the history item that owns the sightseeing.
     */
    public function historyItem()
    {
        return $this->belongsTo(HistoryItem::class);
    }

    // this just get all the user that liked
    public function userFavoriteSightseeing()
    {
        return $this->hasMany(UserFavoriteSightseeing::class,'history_sightseeing_id');
    }

    public function userFavorite()
    {
        return $this->hasOne(UserFavoriteSightseeing::class)->where('user_id', Auth::id());
    }
}
