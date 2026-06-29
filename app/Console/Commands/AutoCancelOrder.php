<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
<<<<<<< HEAD

class AutoCancelOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-cancel-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the autoCancelOrder.js file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $node_path = env('NODE_PATH', '');

        if (! empty($node_path)) {

            // Run the JS file using Node.js
            $command = $node_path . ' --no-experimental-fetch --max-old-space-size=1024 ' . storage_path('app/firebase/autoCancelOrder.js');
            \Log::info("Running command: " . $command);
            $output = shell_exec($command . " /dev/null 2>&1");

            \Log::info('Auto Cancel Output: ' . $output);

            $this->info('Auto Cancel process executed.');
        } else {

            // Log the output
            \Log::info('Auto Cancel Output: Node path is not defined');
        }
=======
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
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    }
}
