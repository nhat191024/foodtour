<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'name',
        'calculate_history_id',
    ];

    public function calculateHistory()
    {
        return $this->belongsTo(CalculateHistory::class, 'calculate_history_id');
    }
}
