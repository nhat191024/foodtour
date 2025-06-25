<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteFood extends Model
{
    protected $table = 'user_favorite_foods';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'history_food_id',
        'user_id'
    ];

    /**
     * Get the history item that owns the food.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function historyFood()
    {
        return $this->belongsTo(HistoryFood::class, 'history_food_id');
    }
}
