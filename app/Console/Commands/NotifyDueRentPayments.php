<?php

namespace App\Console\Commands;

use App\Models\RentDueItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class NotifyDueRentPayments extends Command
{
    protected $signature = 'wasal:notify-due-rent-payments {--days=1}';

    protected $description = 'Create admin tasks for upcoming and overdue rent payments';

    public function handle(): int
    {
        $admin = User::role(['system_admin', 'property_management'])
            ->where('status', 'active')
            ->first();

        if (! $admin) {
            $this->warn('No active property management user found.');

            return self::SUCCESS;
        }

        $until = now()
            ->addDays((int) $this->option('days'))
            ->toDateString();

        $items = RentDueItem::query()
            ->with(['tenancy.tenant', 'tenancy.unit.property'])
            ->whereIn('status', [
                RentDueItem::STATUS_DUE,
                RentDueItem::STATUS_PARTIAL,
                RentDueItem::STATUS_OVERDUE,
            ])
            ->whereColumn('paid_amount', '<', 'amount')
            ->whereDate('due_date', '<=', $until)
            ->orderBy('due_date')
            ->get();

        foreach ($items as $item) {
            $exists = Task::query()
                ->where('related_type', 'rent_due_item')
                ->where('related_id', $item->id)
                ->whereIn('status', [
                    Task::STATUS_PENDING,
                    Task::STATUS_IN_PROGRESS,
                ])
                ->exists();

            if ($exists) {
                continue;
            }

            Task::create([
                'title' => 'متابعة استحقاق إيجار '.$item->due_date->format('Y-m-d'),
                'description' => 'المستأجر: '.$item->tenancy->tenant->name
                    .' — العقار: '.$item->tenancy->unit->property->internal_code
                    .' — المتبقي: '.number_format(
                        max(0, (float) $item->amount - (float) $item->paid_amount),
                        2
                    )
                    .' '.($item->currency?->code ?? ''),
                'related_type' => 'rent_due_item',
                'related_id' => $item->id,
                'assigned_to_user_id' => $admin->id,
                'created_by_user_id' => $admin->id,
                'due_at' => $item->due_date->copy()->setTime(9, 0),
                'notify_before_minutes' => 1440,
                'status' => Task::STATUS_PENDING,
            ]);

            $this->line('Created reminder for due item #'.$item->id);
        }

        return self::SUCCESS;
    }
}
