<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = [
        'name',
        'price',
        'calculate_history_id',
    ];

    public function calculateHistory()
    {
        return $this->belongsTo(CalculateHistory::class, 'calculate_history_id');
    }
}
