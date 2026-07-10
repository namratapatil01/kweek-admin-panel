<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class Cashback extends KweekModel
{
    protected $table = 'cashbacks';

    protected $casts = [
        'payload' => 'array',
        'customerIds' => 'array',
        'paymentMethods' => 'array',
        'allCustomer' => 'boolean',
        'allPayment' => 'boolean',
        'isEnabled' => 'boolean',
        'cashbackAmount' => 'float',
        'cashbackValue' => 'float',
        'maximumDiscount' => 'float',
        'minumumPurchaseAmount' => 'float',
        'startDate' => 'datetime',
        'endDate' => 'datetime',
        'createdAt' => 'datetime',
    ];
}
