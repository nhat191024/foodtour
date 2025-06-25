<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripHistoryCost extends Model
{
    protected $table = 'trip_history_costs';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'value',
        'history_id'
    ];

    /**Tạo lúc
     * Get the history item that owns the food.
     */
    public function history()
    {
        return $this->belongsTo(History::class, 'history_id');
    }
}
