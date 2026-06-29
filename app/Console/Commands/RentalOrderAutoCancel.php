<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
<<<<<<< HEAD

class RentalOrderAutoCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rental-order-auto-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the rentalOrderAutoCancel.js file to cancel rental orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $node_path = env('NODE_PATH', '');

        if (! empty($node_path)) {

            // Run the JS file using Node.js
            $command = $node_path . ' --no-experimental-fetch --max-old-space-size=1024 ' . storage_path('app/firebase/rentalOrderAutoCancel.js');
            \Log::info("Running command: " . $command);
            $output = shell_exec($command . " /dev/null 2>&1");

            \Log::info('Rental Order Auto Cancel Output: ' . $output);

            $this->info('Rental Order Auto Cancel process executed.');
        } else {

            // Log the output
            \Log::info('Rental Order Auto Cancel Output: Node path is not defined');
        }
=======
use Illuminate\Support\Facades\DB;

class RentalOrderAutoCancel extends Command
{
    protected $signature = 'app:rental-order-auto-cancel';

    protected $description = 'Cancel stale rental orders via MySQL';

    public function handle(): int
    {
        $minutes = (int) config('kweek.orders.auto_cancel_minutes', 30);
        $cutoff = now()->subMinutes($minutes);

        $updated = DB::table('rental_orders')
            ->whereIn('status', ['Order Placed', 'In Progress', 'Pending'])
            ->where(function ($query) use ($cutoff) {
                $query->where('created_at', '<', $cutoff)
                    ->orWhere('createdAt', '<', $cutoff);
            })
            ->update(['status' => 'Order Cancelled', 'updated_at' => now()]);

        $this->info("Cancelled {$updated} rental order(s).");

        return self::SUCCESS;
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    }
}
