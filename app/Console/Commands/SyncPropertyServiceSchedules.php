<?php

namespace App\Console\Commands;

use App\Services\PropertyServiceScheduleService;
use Illuminate\Console\Command;

class SyncPropertyServiceSchedules extends Command
{
    protected $signature = 'wasal:sync-property-service-schedules';

    protected $description = 'Create maintenance work orders for upcoming property service schedules';

    public function handle(PropertyServiceScheduleService $service): int
    {
        $count = $service->generateDueTasks();

        $this->info("Created {$count} scheduled property service work order(s).");

        return self::SUCCESS;
    }
}
