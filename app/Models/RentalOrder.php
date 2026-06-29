<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class RentalOrder extends KweekModel
{
    protected $table = 'rental_orders';

    protected $casts = [
        'payload' => 'array',
        'taxSetting' => 'array',
        'author' => 'array',
        'driver' => 'array',
        'rejectedByDrivers' => 'array',
        'createdAt' => 'datetime',
    ];
}
