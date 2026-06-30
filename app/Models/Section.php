<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;

class Section extends KweekModel
{
    protected $table = 'sections';

    protected $casts = [
        'payload' => 'array',
        'isActive' => 'boolean',
        'dine_in_active' => 'boolean',
        'is_product_details' => 'boolean',
        'enableCashbackOffer' => 'boolean',
    ];

    public function vendors()
    {
        return $this->hasMany(Vendor::class, 'section_id', 'id');
    }
}
