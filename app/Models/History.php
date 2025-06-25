<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'start_date',
        'end_date',
        'cost',
        'description',
        'company',
        'interests',
    ];

    /**
     * Get the user that owns the history.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the history items for the history.
     */
    public function items()
    {
        return $this->hasMany(HistoryItem::class);
    }

    public function tripCosts()
    {
        return $this->hasMany(TripHistoryCost::class);
    }
}
