<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class ParcelOrder extends KweekModel
{
    protected $table = 'parcel_orders';

    protected $casts = [
        'payload' => 'array',
        'taxSetting' => 'array',
        'author' => 'array',
        'driver' => 'array',
        'receiver' => 'array',
        'sender' => 'array',
        'rejectedByDrivers' => 'array',
        'createdAt' => 'datetime',
    ];
}
