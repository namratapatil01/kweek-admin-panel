<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduleRide extends Command
{
    protected $signature = 'app:schedule-ride';

    protected $description = 'Process scheduled rides via MySQL';

    public function handle(): int
    {
        Log::info('Scheduled ride processing is handled by MySQL-backed application services.');

        return self::SUCCESS;
    }
}
