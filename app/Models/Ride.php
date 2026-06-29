<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class Ride extends KweekModel
{
    protected $table = 'rides';

    protected $casts = [
        'payload' => 'array',
        'taxSetting' => 'array',
        'author' => 'array',
        'driver' => 'array',
        'rejectedByDrivers' => 'array',
        'createdAt' => 'datetime',
        'scheduleDateTime' => 'datetime',
    ];
}
