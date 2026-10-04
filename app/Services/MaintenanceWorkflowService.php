<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;

class MaintenanceWorkflowService
{
    public function syncTask(MaintenanceRequest $request): ?Task
    {
        $task = Task::query()
            ->where('related_type', 'maintenance_request')
            ->where('related_id', $request->id)
            ->latest('id')
            ->first();

        if ($request->status === MaintenanceRequest::STATUS_COMPLETED) {
            if ($task) {
                $task->update(['status' => Task::STATUS_COMPLETED]);
            }

            if ($request->serviceSchedule) {
                $request->serviceSchedule->update([
                    'last_completed_at' => $request->completed_at ?? now(),
                ]);
            }

            return $task;
        }

        if ($request->status === MaintenanceRequest::STATUS_CANCELLED) {
            if ($task) {
                $task->update(['status' => Task::STATUS_CANCELLED]);
            }

            return $task;
        }

        $assigneeId = $request->assigned_to_user_id
            ?: User::role(['property_management', 'system_admin'])
                ->where('status', 'active')
                ->value('id');

        if (! $assigneeId) {
            return $task;
        }

        $taskStatus = $request->status === MaintenanceRequest::STATUS_IN_PROGRESS
            ? Task::STATUS_IN_PROGRESS
            : Task::STATUS_PENDING;

        $dueAt = $request->scheduled_at ?? now()->addDay();

        $payload = [
            'title' => 'صيانة: '.$request->title,
            'description' => 'الطلب '.$request->reference_number
                .' — العقار: '.($request->property?->internal_code ?? '—'),
            'assigned_to_user_id' => $assigneeId,
            'due_at' => $dueAt,
            'recurrence' => Task::RECURRENCE_ONCE,
            'status' => $taskStatus,
            'notify_before_minutes' => $request->serviceSchedule?->notify_before_minutes ?? 1440,
        ];

        if ($task) {
            if ($task->due_at?->equalTo($dueAt) !== true) {
                $payload['notified_at'] = null;
            }

            $task->update($payload);
        } else {
            $task = Task::create($payload + [
                'related_type' => 'maintenance_request',
                'related_id' => $request->id,
                'created_by_user_id' => auth()->id() ?: $request->created_by_user_id,
            ]);
        }

        if ($request->priority === MaintenanceRequest::PRIORITY_URGENT) {
            Notification::updateOrCreate(
                [
                    'user_id' => $assigneeId,
                    'type' => 'maintenance_urgent',
                    'title' => 'صيانة عاجلة: '.$request->title,
                ],
                [
                    'body' => 'الطلب '.$request->reference_number
                        .' يحتاج متابعة عاجلة في العقار '
                        .($request->property?->internal_code ?? '—').'.',
                    'data' => [
                        'maintenance_request_id' => $request->id,
                        'task_id' => $task->id,
                    ],
                    'read_at' => null,
                ],
            );
        }

        return $task;
    }
}
