<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryMotel extends Model
{
    protected $table = 'history_motels';

    protected $fillable = [
        'history_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'description',
    ];

    public function history()
    {
        return $this->belongsTo(History::class, 'history_id');
    }
}
