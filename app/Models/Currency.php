<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class Currency extends KweekModel
{
    protected $table = 'currencies';

    protected $casts = [
        'payload' => 'array',
        'isActive' => 'boolean',
        'symbolAtRight' => 'boolean',
    ];
}
