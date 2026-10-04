<?php

namespace App\Services;

use App\Models\RentDueItem;
use App\Models\Tenancy;
use Illuminate\Support\Carbon;

class RentDueScheduleService
{
    public function generateForTenancy(Tenancy $tenancy, ?Carbon $through = null): int
    {
        if ($tenancy->status !== Tenancy::STATUS_ACTIVE) {
            $this->cancelFutureDues($tenancy);

            return 0;
        }

        if (! $tenancy->auto_generate_dues) {
            $this->syncStatuses($tenancy);

            return 0;
        }

        $through ??= $tenancy->ends_at?->copy() ?? now()->copy()->addYear();

        if ($tenancy->ends_at && $through->greaterThan($tenancy->ends_at)) {
            $through = $tenancy->ends_at->copy();
        }

        $preferredDay = (int) ($tenancy->due_day ?: $tenancy->starts_at->day);
        $dueDate = $this->firstDueDate($tenancy);
        $dates = [];

        while ($dueDate->lte($through)) {
            $dates[] = $dueDate->toDateString();
            $dueDate = $this->nextDueDate(
                date: $dueDate,
                frequency: $tenancy->payment_frequency,
                preferredDay: $preferredDay,
            );
        }

        if ($dates !== []) {
            RentDueItem::query()
                ->where('tenancy_id', $tenancy->id)
                ->whereDate('due_date', '>=', today())
                ->where('paid_amount', '<=', 0)
                ->whereNotIn('status', [RentDueItem::STATUS_PAID])
                ->whereNotIn('due_date', $dates)
                ->update(['status' => RentDueItem::STATUS_CANCELLED]);
        }

        $created = 0;

        foreach ($dates as $date) {
            $item = RentDueItem::query()
                ->firstOrNew([
                    'tenancy_id' => $tenancy->id,
                    'due_date' => $date,
                ]);

            if (! $item->exists) {
                $created++;
            }

            if (! $item->exists || (float) $item->paid_amount <= 0) {
                $carbonDate = Carbon::parse($date);

                $item->fill([
                    'amount' => $tenancy->rent_amount,
                    'currency_id' => $tenancy->currency_id,
                    'status' => $this->statusForDate(
                        $carbonDate,
                        (int) $tenancy->grace_days,
                    ),
                    'paid_amount' => $item->paid_amount ?? 0,
                ]);

                $item->save();
            }
        }

        $this->syncStatuses($tenancy);

        return $created;
    }

    public function generateForActiveTenancies(): int
    {
        $count = 0;

        Tenancy::query()
            ->where('status', Tenancy::STATUS_ACTIVE)
            ->where('auto_generate_dues', true)
            ->orderBy('id')
            ->each(function (Tenancy $tenancy) use (&$count): void {
                $count += $this->generateForTenancy($tenancy);
            });

        return $count;
    }

    public function syncStatuses(?Tenancy $tenancy = null): int
    {
        $updated = 0;

        $query = RentDueItem::query()
            ->whereNotIn('status', [
                RentDueItem::STATUS_PAID,
                RentDueItem::STATUS_CANCELLED,
            ]);

        if ($tenancy) {
            $query->where('tenancy_id', $tenancy->id);
        }

        $query
            ->with('tenancy')
            ->orderBy('id')
            ->each(function (RentDueItem $item) use (&$updated): void {
                $remaining = max(0, (float) $item->amount - (float) $item->paid_amount);

                $status = match (true) {
                    $remaining <= 0 => RentDueItem::STATUS_PAID,
                    (float) $item->paid_amount > 0 => RentDueItem::STATUS_PARTIAL,
                    $this->isOverdue($item) => RentDueItem::STATUS_OVERDUE,
                    default => RentDueItem::STATUS_DUE,
                };

                if ($item->status !== $status) {
                    $item->update(['status' => $status]);
                    $updated++;
                }
            });

        return $updated;
    }

    public function cancelFutureDues(Tenancy $tenancy): int
    {
        return RentDueItem::query()
            ->where('tenancy_id', $tenancy->id)
            ->whereDate('due_date', '>', today())
            ->where('paid_amount', '<=', 0)
            ->whereNotIn('status', [
                RentDueItem::STATUS_PAID,
                RentDueItem::STATUS_CANCELLED,
            ])
            ->update(['status' => RentDueItem::STATUS_CANCELLED]);
    }

    public function isOverdue(RentDueItem $item): bool
    {
        $graceDays = (int) ($item->tenancy?->grace_days ?? 0);

        return $item->due_date
            ->copy()
            ->addDays($graceDays)
            ->lt(today());
    }

    private function firstDueDate(Tenancy $tenancy): Carbon
    {
        $startsAt = $tenancy->starts_at->copy()->startOfDay();
        $preferredDay = (int) ($tenancy->due_day ?: $startsAt->day);

        $candidate = $startsAt->copy()
            ->startOfMonth()
            ->day(min($preferredDay, $startsAt->daysInMonth));

        if ($candidate->lt($startsAt)) {
            $candidate = $this->nextDueDate(
                date: $candidate,
                frequency: $tenancy->payment_frequency,
                preferredDay: $preferredDay,
            );
        }

        return $candidate;
    }

    private function nextDueDate(
        Carbon $date,
        string $frequency,
        int $preferredDay,
    ): Carbon {
        $months = match ($frequency) {
            Tenancy::FREQUENCY_QUARTERLY => 3,
            Tenancy::FREQUENCY_SEMIANNUAL => 6,
            Tenancy::FREQUENCY_ANNUAL => 12,
            default => 1,
        };

        $next = $date->copy()
            ->startOfMonth()
            ->addMonthsNoOverflow($months);

        return $next->day(min($preferredDay, $next->daysInMonth));
    }

    private function statusForDate(Carbon $date, int $graceDays): string
    {
        return $date->copy()->addDays($graceDays)->lt(today())
            ? RentDueItem::STATUS_OVERDUE
            : RentDueItem::STATUS_DUE;
    }
}
