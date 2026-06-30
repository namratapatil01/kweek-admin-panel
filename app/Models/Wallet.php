<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class Wallet extends KweekModel
{
    protected $table = 'wallet';

    protected $casts = [
        'isTopUp' => 'boolean',
        'amount' => 'float',
        'date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id', 'id');
    }
}
