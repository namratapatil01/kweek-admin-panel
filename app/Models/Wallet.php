<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Model;

/**
 * Wallet model — represents rows in the `wallet` table.
 * Used for wallet transactions.
 */
class Wallet extends Model
{
    protected $table = 'wallet';

    // Firebase IDs are strings, not auto-increment integers
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'user_id', 'amount', 'note', 'isTopUp',
        'payment_method', 'payment_status', 'transactionUser',
        'order_id', 'date'
    ];

    protected $casts = [
        'isTopUp' => 'boolean',
        'amount'  => 'float',
        'date'    => 'datetime',
=======
use App\Models\Concerns\KweekModel;

class Wallet extends KweekModel
{
    protected $table = 'wallet';

    protected $casts = [
        'isTopUp' => 'boolean',
        'amount' => 'float',
        'date' => 'datetime',
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    ];

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'user_id', 'id');
    }
}
