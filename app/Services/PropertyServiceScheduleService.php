<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\PropertyService;
use App\Models\PropertyServiceSchedule;
use App\Models\User;
use Illuminate\Support\Carbon;

class PropertyServiceScheduleService
{
    public function generateDueTasks(?Carbon $now = null): int
    {
        $now ??= now();
        $count = 0;

        PropertyServiceSchedule::query()
            ->with(['service', 'property', 'unit', 'vendor'])
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

                if (! $assigneeId) {
                    return;
                }

                $request = MaintenanceRequest::firstOrCreate(
                    [
                        'property_service_schedule_id' => $schedule->id,
                        'scheduled_at' => $dueAt,
                    ],
                    [
                        'property_id' => $schedule->property_id,
                        'property_unit_id' => $schedule->property_unit_id,
                        'property_service_id' => $schedule->property_service_id,
                        'property_vendor_id' => $schedule->property_vendor_id,
                        'assigned_to_user_id' => $assigneeId,
                        'created_by_user_id' => $assigneeId,
                        'title' => $schedule->service->name_ar,
                        'description' => 'خدمة دورية تم إنشاؤها تلقائيًا من جدول الخدمات.',
                        'priority' => MaintenanceRequest::PRIORITY_NORMAL,
                        'status' => MaintenanceRequest::STATUS_SCHEDULED,
                        'estimated_cost' => $schedule->estimated_cost,
                        'currency_id' => $schedule->currency_id,
                        'cost_bearer' => MaintenanceRequest::COST_OWNER,
                        'is_paid' => false,
                        'notes' => 'مولد تلقائيًا من جدول الخدمة #'.$schedule->id,
                    ],
                );

                if ($schedule->service?->creates_task) {
                    app(MaintenanceWorkflowService::class)->syncTask($request);
                }

                if ($request->wasRecentlyCreated) {
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
