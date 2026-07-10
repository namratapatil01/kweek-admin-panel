<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class CashbackRedeem extends KweekModel
{
    protected $table = 'cashback_redeems';

    protected $casts = [
        'payload' => 'array',
        'createdAt' => 'datetime',
    ];
}
