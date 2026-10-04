<?php

namespace App\Console\Commands;

use App\Models\OwnerSettlement;
use App\Models\PropertyDocument;
use App\Models\PropertyManagementAgreement;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class SyncPropertyManagementAlerts extends Command
{
    protected $signature = 'wasal:sync-property-management-alerts';

    protected $description = 'Create follow-up tasks for expiring agreements, documents, and pending owner settlements';

    public function handle(): int
    {
        $fallbackUserId = User::role(['property_management', 'system_admin'])
            ->where('status', 'active')
            ->value('id');

        if (! $fallbackUserId) {
            $this->warn('No active property management user found.');

            return self::SUCCESS;
        }

        $created = 0;

        PropertyManagementAgreement::query()
            ->where('status', PropertyManagementAgreement::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '>=', today())
            ->whereDate('ends_at', '<=', today()->addDays(60))
            ->each(function (PropertyManagementAgreement $agreement) use ($fallbackUserId, &$created): void {
                $days = max(0, (int) ($agreement->renewal_notice_days ?? 30));

                if ($agreement->ends_at->gt(today()->addDays($days))) {
                    return;
                }

                $created += $this->createTask(
                    relatedType: 'property_management_agreement',
                    relatedId: $agreement->id,
                    title: 'تجديد اتفاق إدارة '.$agreement->agreement_number,
                    description: 'اتفاق إدارة العقار '.($agreement->property?->internal_code ?? '—').' يقترب من الانتهاء.',
                    dueAt: $agreement->ends_at->copy()->setTime(9, 0),
                    assigneeId: $agreement->assigned_manager_user_id ?: $fallbackUserId,
                    creatorId: $fallbackUserId,
                );
            });

        PropertyDocument::query()
            ->where('status', PropertyDocument::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [today(), today()->addDays(30)])
            ->each(function (PropertyDocument $document) use ($fallbackUserId, &$created): void {
                $created += $this->createTask(
                    relatedType: 'property_document',
                    relatedId: $document->id,
                    title: 'تجديد مستند: '.$document->title,
                    description: 'المستند المرتبط بالعقار '.($document->property?->internal_code ?? '—').' يقترب من الانتهاء.',
                    dueAt: $document->expires_at->copy()->setTime(9, 0),
                    assigneeId: $fallbackUserId,
                    creatorId: $fallbackUserId,
                );
            });

        OwnerSettlement::query()
            ->where('status', OwnerSettlement::STATUS_APPROVED)
            ->whereNotNull('approved_at')
            ->where('approved_at', '<=', now()->subDays(3))
            ->each(function (OwnerSettlement $settlement) use ($fallbackUserId, &$created): void {
                $created += $this->createTask(
                    relatedType: 'owner_settlement',
                    relatedId: $settlement->id,
                    title: 'سداد تسوية مالك '.$settlement->settlement_number,
                    description: 'تسوية معتمدة منذ أكثر من 3 أيام ولم تسجل كمدفوعة بعد.',
                    dueAt: now()->addHour(),
                    assigneeId: $fallbackUserId,
                    creatorId: $fallbackUserId,
                );
            });

        $this->info("Created {$created} property management alert task(s).");

        return self::SUCCESS;
    }

    private function createTask(
        string $relatedType,
        int $relatedId,
        string $title,
        string $description,
        $dueAt,
        int $assigneeId,
        int $creatorId,
    ): int {
        $exists = Task::query()
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
            ->exists();

        if ($exists) {
            return 0;
        }

        Task::create([
            'title' => $title,
            'description' => $description,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'assigned_to_user_id' => $assigneeId,
            'created_by_user_id' => $creatorId,
            'due_at' => $dueAt,
            'recurrence' => Task::RECURRENCE_ONCE,
            'status' => Task::STATUS_PENDING,
            'notify_before_minutes' => 1440,
        ]);

        return 1;
    }
}
