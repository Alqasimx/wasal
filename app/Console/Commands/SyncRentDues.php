<?php

namespace App\Console\Commands;

use App\Services\RentDueScheduleService;
use Illuminate\Console\Command;

class SyncRentDues extends Command
{
    protected $signature = 'wasal:sync-rent-dues';

    protected $description = 'Generate missing rent dues and refresh overdue statuses';

    public function handle(RentDueScheduleService $service): int
    {
        $created = $service->generateForActiveTenancies();
        $updated = $service->syncStatuses();

        $this->info("Created {$created} rent due item(s); refreshed {$updated} status(es).");

        return self::SUCCESS;
    }
}
