<?php

namespace App\Models;

use App\Models\Concerns\KweekModel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Mobile app users (customers, drivers, vendors, etc.).
 */
class AppUser extends Authenticatable
{
    use HasApiTokens;
    use \App\Traits\HasStringPrimaryKey;
    use \App\Traits\HasJsonPayload;

    protected $table = 'app_users';

    protected $guarded = [];

    protected $hidden = ['password'];

    protected $casts = [
        'active' => 'boolean',
        'isActive' => 'boolean',
        'isOwner' => 'boolean',
        'isDocumentVerify' => 'boolean',
        'wallet_amount' => 'float',
        'orderCompleted' => 'integer',
        'userBankDetails' => 'array',
        'settings' => 'array',
        'shippingAddress' => 'array',
        'carInfo' => 'array',
        'payload' => 'array',
        'createdAt' => 'datetime',
        'lastOnlineTimestamp' => 'datetime',
    ];

    protected $appends = ['location'];

    public function getLocationAttribute(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function vendorOrders()
    {
        return $this->hasMany(VendorOrder::class, 'authorID', 'id');
    }

    public function walletEntries()
    {
        return $this->hasMany(Wallet::class, 'user_id', 'id');
    }

    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver')->where('isOwner', false);
    }

    public function scopeApproved($query)
    {
        return $query->where('isDocumentVerify', true);
    }

    public function scopePending($query)
    {
        return $query->where('isDocumentVerify', false);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    public function toDocumentArray(): array
    {
        $data = array_merge($this->attributesToArray(), $this->payload ?? []);
        unset($data['payload'], $data['password'], $data['created_at'], $data['updated_at']);

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
