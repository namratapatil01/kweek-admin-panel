<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
<<<<<<< HEAD

class SendScheduledOrderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-scheduled-order-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the scheduleNotification.js file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $node_path = env('NODE_PATH', '');

        if (! empty($node_path)) {

            // Run the JS file using Node.js
            $command = $node_path . ' --no-experimental-fetch --max-old-space-size=1024 ' . storage_path('app/firebase/scheduleNotification.js');
            \Log::info("Running command: " . $command);
            $output = shell_exec($command . " /dev/null 2>&1");

            \Log::info('Schedule notification Output: ' . $output);

            $this->info('Schedule notification process executed.');
        } else {

            // Log the output
            \Log::info('schedule notification Output: Node path is not defined');
        }
=======
use Illuminate\Support\Facades\Log;

class SendScheduledOrderNotification extends Command
{
    protected $signature = 'app:send-scheduled-order-notification';

    protected $description = 'Send scheduled order notifications via MySQL';

    public function handle(): int
    {
        Log::info('Scheduled order notifications are handled by MySQL-backed application services.');

        return self::SUCCESS;
>>>>>>> 4c9a071090dc3b20faed875c7d70567ba65ae18f
    }
}
