<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledOrderNotification extends Command
{
    protected $signature = 'app:send-scheduled-order-notification';

    protected $description = 'Send scheduled order notifications via MySQL';

    public function handle(): int
    {
        Log::info('Scheduled order notifications are handled by MySQL-backed application services.');

        return self::SUCCESS;
    }
}
