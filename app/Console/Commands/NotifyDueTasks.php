<?php

namespace App\Console\Commands;

use App\Services\TaskNotificationService;
use Illuminate\Console\Command;

class NotifyDueTasks extends Command
{
    protected $signature = 'wasal:notify-due-tasks';

    protected $description = 'Create in-app notifications for tasks approaching their due time';

    public function handle(TaskNotificationService $service): int
    {
        $count = $service->notifyDueTasks();

        $this->info("Created {$count} task notification(s).");

        return self::SUCCESS;
    }
}
