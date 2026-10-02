<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Support\Carbon;

class TaskNotificationService
{
    public function notifyDueTasks(?Carbon $now = null): int
    {
        $now ??= now();
        $count = 0;

        Task::query()
            ->with('assignee')
            ->awaitingNotification($now)
            ->orderBy('id')
            ->each(function (Task $task) use (&$count): void {
                Notification::create([
                    'user_id' => $task->assigned_to_user_id,
                    'type' => 'task_due_soon',
                    'title' => 'مهمة قريبة الاستحقاق',
                    'body' => $task->title,
                    'data' => [
                        'task_id' => $task->id,
                        'due_at' => $task->due_at?->toISOString(),
                        'recurrence' => $task->recurrence,
                    ],
                ]);

                $task->forceFill(['notified_at' => now()])->saveQuietly();
                $count++;
            });

        return $count;
    }
}
