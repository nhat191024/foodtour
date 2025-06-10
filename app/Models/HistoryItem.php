<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'history_id',
        'day_number',
        'day_time',
    ];

    /**
     * Get the history that owns the history item.
     */
    public function history()
    {
        return $this->belongsTo(History::class);
    }

    /**
     * Get the sightseeing records associated with the history item.
     */
    public function sightseeing()
    {
        return $this->hasMany(HistorySightseeing::class);
    }

    /**
     * Get the food records associated with the history item.
     */
    public function food()
    {
        return $this->hasMany(HistoryFood::class);
    }
}
