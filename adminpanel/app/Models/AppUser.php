<?php

namespace App\Models;

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
    ];

    protected $appends = ['location'];

    public function getLocationAttribute()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                            */
    /* ------------------------------------------------------------------ */

    /** Only drivers (non-owner). */
    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver')->where('isOwner', false);
    }

    /** Document-verified drivers. */
    public function scopeApproved($query)
    {
        return $query->where('isDocumentVerify', true);
    }

    /** Drivers pending document verification. */
    public function scopePending($query)
    {
        return $query->where('isDocumentVerify', false);
    }

    /* ------------------------------------------------------------------ */
    /*  Accessors                                                         */
    /* ------------------------------------------------------------------ */

    /** Full name helper used by the DataTable. */
    public function getFullNameAttribute(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }
}
