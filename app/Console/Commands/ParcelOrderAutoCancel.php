<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ParcelOrderAutoCancel extends Command
{
    protected $signature = 'app:parcel-order-auto-cancel';

    protected $description = 'Cancel stale parcel orders via MySQL';

    public function handle(): int
    {
        $minutes = (int) config('kweek.orders.auto_cancel_minutes', 30);
        $cutoff = now()->subMinutes($minutes);

        $updated = DB::table('parcel_orders')
            ->whereIn('status', ['Order Placed', 'In Progress', 'Pending'])
            ->where(function ($query) use ($cutoff) {
                $query->where('created_at', '<', $cutoff)
                    ->orWhere('createdAt', '<', $cutoff);
            })
            ->update(['status' => 'Order Cancelled', 'updated_at' => now()]);

        $this->info("Cancelled {$updated} parcel order(s).");

        return self::SUCCESS;
    }
}
