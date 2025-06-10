<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
