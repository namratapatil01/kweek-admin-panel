<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneOtp extends Model
{
    protected $table = 'phone_otps';

    protected $fillable = [
        'id',
        'phone_number',
        'country_code',
        'otp_hash',
        'attempts',
        'expires_at',
        'role',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
}
