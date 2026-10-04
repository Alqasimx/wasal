<?php

namespace App\Console\Commands;

use App\Services\PropertyServiceScheduleService;
use Illuminate\Console\Command;

class SyncPropertyServiceSchedules extends Command
{
    protected $signature = 'wasal:sync-property-service-schedules';

    protected $description = 'Create tasks for upcoming property-management service schedules';

    public function handle(PropertyServiceScheduleService $service): int
    {
        $count = $service->generateDueTasks();

        $this->info("Created {$count} scheduled property-service task(s).");

        return self::SUCCESS;
    }
}
