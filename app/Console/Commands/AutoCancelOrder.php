<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCancelOrder extends Command
{
    protected $signature = 'app:auto-cancel-order';

    protected $description = 'Cancel stale vendor orders via MySQL';

    public function handle(): int
    {
        $minutes = (int) config('kweek.orders.auto_cancel_minutes', 30);
        $cutoff = now()->subMinutes($minutes);

        $updated = DB::table('vendor_orders')
            ->whereIn('status', ['Order Placed', 'In Progress', 'Pending'])
            ->where(function ($query) use ($cutoff) {
                $query->where('created_at', '<', $cutoff)
                    ->orWhere('createdAt', '<', $cutoff);
            })
            ->update(['status' => 'Order Cancelled', 'updated_at' => now()]);

        $this->info("Cancelled {$updated} vendor order(s).");

        return self::SUCCESS;
    }
}
