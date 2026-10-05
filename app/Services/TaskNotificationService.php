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
            ->whereNotNull('assigned_to_user_id')
            ->whereNotNull('due_at')
            ->whereNull('notified_at')
            ->whereNotIn('status', [
                Task::STATUS_COMPLETED,
                Task::STATUS_CANCELLED,
            ])
            ->orderBy('id')
            ->get()
            ->filter(function (Task $task) use ($now): bool {
                $notifyAt = $task->due_at
                    ->copy()
                    ->subMinutes((int) ($task->notify_before_minutes ?? 0));

                return $notifyAt->lte($now);
            })
            ->each(function (Task $task) use (&$count, $now): void {
                [$type, $title] = $this->notificationMeta($task);

                Notification::create([
                    'user_id' => $task->assigned_to_user_id,
                    'type' => $type,
                    'title' => $title,
                    'body' => $task->title,
                    'data' => [
                        'task_id' => $task->id,
                        'related_type' => $task->related_type,
                        'related_id' => $task->related_id,
                        'due_at' => $task->due_at?->toISOString(),
                        'recurrence' => $task->recurrence,
                    ],
                ]);

                $task->forceFill(['notified_at' => $now])->saveQuietly();
                $count++;
            });

        return $count;
    }

    private function notificationMeta(Task $task): array
    {
        return match ($task->related_type) {
            'rent_due_item' => ['rent_due', 'استحقاق إيجار'],
            'maintenance_request' => ['maintenance_due', 'موعد صيانة'],
            'property_service_schedule' => ['service_due', 'خدمة عقار مجدولة'],
            'tenancy' => ['tenancy_due', 'متابعة عقد إيجار'],
            'property_management_agreement' => ['agreement_due', 'متابعة اتفاق إدارة'],
            'property_inspection' => ['inspection_due', 'موعد معاينة عقار'],
            'property_document' => ['document_due', 'مستند يقترب من الانتهاء'],
            'property_expense' => ['expense_due', 'مراجعة مصروف عقار'],
            'owner_settlement' => ['owner_settlement_due', 'تسوية مالك'],
            'property_unit' => ['occupancy_due', 'متابعة إشغال وحدة'],
            'property_vendor' => ['vendor_due', 'متابعة مورد / فني'],
            default => ['task_due_soon', 'مهمة قريبة الاستحقاق'],
        };
    }
}
