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

    public function parcelOrders()
    {
        return $this->hasMany(ParcelOrder::class, 'authorID', 'id');
    }

    public function rentalOrders()
    {
        return $this->hasMany(RentalOrder::class, 'authorID', 'id');
    }

    public function rides()
    {
        return $this->hasMany(Ride::class, 'authorID', 'id');
    }

    public function providerOrders()
    {
        return $this->hasMany(ProviderOrder::class, 'authorID', 'id');
    }

    public function walletEntries()
    {
        return $this->hasMany(Wallet::class, 'user_id', 'id');
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver')->where('isOwner', false);
    }

    public function scopeVendors($query)
    {
        return $query->where('role', 'vendor');
    }

    public function scopeProviders($query)
    {
        return $query->where('role', 'provider');
    }

    public function scopeWorkers($query)
    {
        return $query->where('role', 'worker');
    }

    public function providerServices()
    {
        return $this->hasMany(ProviderService::class, 'payload->author', 'id');
    }

    public function providerWorkers()
    {
        return $this->hasMany(ProviderWorker::class, 'providerId', 'id');
    }

    public function providerCoupons()
    {
        return $this->hasMany(ProviderCoupon::class, 'providerId', 'id');
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
