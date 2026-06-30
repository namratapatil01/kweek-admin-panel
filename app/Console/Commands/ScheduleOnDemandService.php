<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScheduleOnDemandService extends Command
{
    protected $signature = 'app:schedule-on-demand-service';

    protected $description = 'Process scheduled on-demand services via MySQL';

    public function handle(): int
    {
        Log::info('Scheduled on-demand service processing is handled by MySQL-backed application services.');

        return self::SUCCESS;
    }
}
