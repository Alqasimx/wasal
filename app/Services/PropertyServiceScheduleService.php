<?php

namespace App\Services;

use App\Models\PropertyService;
use App\Models\PropertyServiceSchedule;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

class PropertyServiceScheduleService
{
    public function generateDueTasks(?Carbon $now = null): int
    {
        $now ??= now();
        $count = 0;

        PropertyServiceSchedule::query()
            ->with(['service', 'property', 'unit'])
            ->where('is_active', true)
            ->whereNotNull('next_due_at')
            ->where('next_due_at', '<=', $now->copy()->addDay())
            ->orderBy('next_due_at')
            ->each(function (PropertyServiceSchedule $schedule) use (&$count): void {
                $dueAt = $schedule->next_due_at->copy();

                $assigneeId = $schedule->assigned_to_user_id
                    ?: User::role(['property_management', 'system_admin'])
                        ->where('status', 'active')
                        ->value('id');

                if ($schedule->service?->creates_task && $assigneeId) {
                    Task::create([
                        'title' => 'خدمة مجدولة: '.$schedule->service->name_ar,
                        'description' => 'العقار: '.$schedule->property->internal_code
                            .($schedule->unit ? ' — الوحدة: '.$schedule->unit->code : ''),
                        'related_type' => 'property_service_schedule',
                        'related_id' => $schedule->id,
                        'assigned_to_user_id' => $assigneeId,
                        'created_by_user_id' => $assigneeId,
                        'due_at' => $dueAt,
                        'recurrence' => Task::RECURRENCE_ONCE,
                        'status' => Task::STATUS_PENDING,
                        'notify_before_minutes' => $schedule->notify_before_minutes,
                    ]);

                    $count++;
                }

                $nextDueAt = $this->calculateNextDueAt(
                    dueAt: $dueAt,
                    frequency: $schedule->frequency,
                    interval: max(1, (int) $schedule->interval_count),
                );

                $schedule->update([
                    'last_generated_due_at' => $dueAt,
                    'next_due_at' => $nextDueAt,
                    'is_active' => $nextDueAt !== null,
                ]);
            });

        return $count;
    }

    public function calculateNextDueAt(
        Carbon $dueAt,
        string $frequency,
        int $interval = 1,
    ): ?Carbon {
        return match ($frequency) {
            PropertyService::FREQUENCY_ONCE => null,
            PropertyService::FREQUENCY_DAILY => $dueAt->copy()->addDays($interval),
            PropertyService::FREQUENCY_WEEKLY => $dueAt->copy()->addWeeks($interval),
            PropertyService::FREQUENCY_MONTHLY => $dueAt->copy()->addMonthsNoOverflow($interval),
            PropertyService::FREQUENCY_QUARTERLY => $dueAt->copy()->addMonthsNoOverflow(3 * $interval),
            PropertyService::FREQUENCY_SEMIANNUAL => $dueAt->copy()->addMonthsNoOverflow(6 * $interval),
            PropertyService::FREQUENCY_ANNUAL => $dueAt->copy()->addYears($interval),
            PropertyService::FREQUENCY_CUSTOM_DAYS => $dueAt->copy()->addDays($interval),
            default => $dueAt->copy()->addMonthsNoOverflow($interval),
        };
    }
}
