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
        'provider' => 'array',
        'createdAt' => 'datetime',
    ];
}
