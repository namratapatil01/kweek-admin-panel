<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class ProviderOrder extends KweekModel
{
    protected $table = 'provider_orders';

    protected $casts = [
        'payload' => 'array',
        'taxSetting' => 'array',
        'author' => 'array',
        'driver' => 'array',
        'vendor' => 'array',
        'provider' => 'array',
        'products' => 'array',
        'address' => 'array',
        'receiver' => 'array',
        'sender' => 'array',
        'rejectedByDrivers' => 'array',
        'createdAt' => 'datetime',
    ];
}
