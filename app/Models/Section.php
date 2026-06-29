<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
=======
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
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
}
