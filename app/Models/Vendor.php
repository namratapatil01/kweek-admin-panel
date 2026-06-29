<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class Vendor extends KweekModel
{
    protected $table = 'vendors';

    protected $casts = [
        'payload' => 'array',
        'photos' => 'array',
        'workingHours' => 'array',
        'filters' => 'array',
        'reststatus' => 'boolean',
        'dine_in_active' => 'boolean',
        'walletAmount' => 'float',
        'reviewsSum' => 'float',
        'createdAt' => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(VendorProduct::class, 'vendorID', 'id');
    }
}
