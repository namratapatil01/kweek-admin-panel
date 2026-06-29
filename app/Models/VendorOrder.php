<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class VendorOrder extends KweekModel
{
    protected $table = 'vendor_orders';

    protected $casts = [
        'payload' => 'array',
        'products' => 'array',
        'taxSetting' => 'array',
        'address' => 'array',
        'author' => 'array',
        'driver' => 'array',
        'vendor' => 'array',
        'rejectedByDrivers' => 'array',
        'takeAway' => 'boolean',
        'createdAt' => 'datetime',
        'scheduleTime' => 'datetime',
    ];

    public function authorUser()
    {
        return $this->belongsTo(AppUser::class, 'authorID', 'id');
    }

    public function vendorRecord()
    {
        return $this->belongsTo(Vendor::class, 'vendorID', 'id');
    }
}
