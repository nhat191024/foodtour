<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryBus extends Model
{
    protected $table = 'history_buses';

    protected $fillable = [
        'history_id',
        'name',
        'address',
        'description',
        'phone',
        'website',
        'departure_time',
        'arrival_time',
        'price',
    ];

    public function history()
    {
        return $this->belongsTo(History::class, 'history_id');
    }
}
