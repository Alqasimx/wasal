<?php

namespace App\Services;

use App\Models\PropertyInspection;
use App\Models\Task;
use App\Models\User;

class InspectionWorkflowService
{
    public function syncTask(PropertyInspection $inspection): ?Task
    {
        $task = Task::query()
            ->where('related_type', 'property_inspection')
            ->where('related_id', $inspection->id)
            ->latest('id')
            ->first();

        if ($inspection->status === PropertyInspection::STATUS_COMPLETED) {
            $task?->update(['status' => Task::STATUS_COMPLETED]);

            return $task;
        }

        if ($inspection->status === PropertyInspection::STATUS_CANCELLED) {
            $task?->update(['status' => Task::STATUS_CANCELLED]);

            return $task;
        }

        $assigneeId = $inspection->inspector_user_id
            ?: User::role(['property_management', 'system_admin'])
                ->where('status', 'active')
                ->value('id');

        if (! $assigneeId) {
            return $task;
        }

        $payload = [
            'title' => 'معاينة: '.$inspection->inspection_number,
            'description' => 'العقار: '.($inspection->property?->internal_code ?? '—')
                .' — النوع: '.$inspection->inspection_type,
            'assigned_to_user_id' => $assigneeId,
            'due_at' => $inspection->scheduled_at,
            'recurrence' => Task::RECURRENCE_ONCE,
            'status' => $inspection->status === PropertyInspection::STATUS_IN_PROGRESS
                ? Task::STATUS_IN_PROGRESS
                : Task::STATUS_PENDING,
            'notify_before_minutes' => 1440,
        ];

        if ($task) {
            if ($task->due_at?->equalTo($inspection->scheduled_at) !== true) {
                $payload['notified_at'] = null;
            }

            $task->update($payload);

            return $task;
        }

        return Task::create($payload + [
            'related_type' => 'property_inspection',
            'related_id' => $inspection->id,
            'created_by_user_id' => auth()->id() ?: $inspection->created_by_user_id,
        ]);
    }
}
