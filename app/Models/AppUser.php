<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Model;

/**
 * AppUser model — represents rows in the `app_users` table.
 * This table was migrated from the Firebase `users` collection and holds
 * drivers, customers, and other app-level user records.
 */
class AppUser extends Model
{
    protected $table = 'app_users';

    // Firebase IDs are strings, not auto-increment integers
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'firstName', 'lastName', 'email', 'phoneNumber',
        'role', 'active', 'isActive', 'isOwner', 'isDocumentVerify',
        'profilePictureURL', 'serviceType', 'sectionId',
        'wallet_amount', 'orderCompleted', 'fcmToken',
        'carName', 'carNumber', 'carColor', 'vehicleType',
        'carPictureURL', 'driverRate', 'carInfo',
        'latitude', 'longitude', 'zoneId', 'countryCode', 'carMakes',
        'vehicleId', 'rideType', 'userBankDetails', 'carProofPictureURL', 'driverProofPictureURL', 'ownerId',
    ];

    protected $casts = [
        'active'            => 'boolean',
        'isActive'          => 'boolean',
        'isOwner'           => 'boolean',
        'isDocumentVerify'  => 'boolean',
        'wallet_amount'     => 'float',
        'orderCompleted'    => 'integer',
        'userBankDetails'   => 'array',
        'carInfo'           => 'array',
=======
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
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    ];

    protected $appends = ['location'];

<<<<<<< HEAD
    public function getLocationAttribute()
=======
    public function getLocationAttribute(): array
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

<<<<<<< HEAD
    /* ------------------------------------------------------------------ */
    /*  Scopes                                                            */
    /* ------------------------------------------------------------------ */

    /** Only drivers (non-owner). */
=======
    public function vendorOrders()
    {
        return $this->hasMany(VendorOrder::class, 'authorID', 'id');
    }

    public function walletEntries()
    {
        return $this->hasMany(Wallet::class, 'user_id', 'id');
    }

>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver')->where('isOwner', false);
    }

<<<<<<< HEAD
    /** Document-verified drivers. */
=======
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    public function scopeApproved($query)
    {
        return $query->where('isDocumentVerify', true);
    }

<<<<<<< HEAD
    /** Drivers pending document verification. */
=======
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    public function scopePending($query)
    {
        return $query->where('isDocumentVerify', false);
    }

<<<<<<< HEAD
    /* ------------------------------------------------------------------ */
    /*  Accessors                                                         */
    /* ------------------------------------------------------------------ */

    /** Full name helper used by the DataTable. */
=======
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    public function getFullNameAttribute(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }
<<<<<<< HEAD
=======

    public function toDocumentArray(): array
    {
        $data = array_merge($this->attributesToArray(), $this->payload ?? []);
        unset($data['payload'], $data['password'], $data['created_at'], $data['updated_at']);

        return array_filter($data, static fn ($v) => $v !== null);
    }
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
}
