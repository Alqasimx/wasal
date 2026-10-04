<?php

namespace App\Services;

use App\Models\RentDueItem;
use App\Models\RentPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentPaymentService
{
    public function record(array $data, User $actor): RentPayment
    {
        return DB::transaction(function () use ($data, $actor): RentPayment {
            $dueItem = RentDueItem::query()
                ->lockForUpdate()
                ->findOrFail($data['rent_due_item_id']);

            $amount = (float) $data['amount'];
            $remaining = max(0, (float) $dueItem->amount - (float) $dueItem->paid_amount);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'يجب أن يكون مبلغ التحصيل أكبر من صفر.',
                ]);
            }

            if ($amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'المبلغ أكبر من الرصيد المتبقي على هذا الاستحقاق.',
                ]);
            }

            if ((int) $data['currency_id'] !== (int) $dueItem->currency_id) {
                throw ValidationException::withMessages([
                    'currency_id' => 'عملة التحصيل يجب أن تطابق عملة الاستحقاق.',
                ]);
            }

            $payment = RentPayment::create([
                ...$data,
                'tenancy_id' => $dueItem->tenancy_id,
                'recorded_by_user_id' => $actor->id,
                'status' => RentPayment::STATUS_POSTED,
            ]);

            $this->refreshDueItem($dueItem);

            app(AuditService::class)->forModel(
                action: 'rent_payment.posted',
                model: $payment,
                newValues: $payment->fresh()->toArray(),
                actor: $actor,
                request: request(),
            );

            return $payment;
        });
    }

    public function void(RentPayment $payment, User $actor, string $reason): void
    {
        DB::transaction(function () use ($payment, $actor, $reason): void {
            $payment = RentPayment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->status === RentPayment::STATUS_VOIDED) {
                return;
            }

            $oldValues = $payment->toArray();

            $payment->update([
                'status' => RentPayment::STATUS_VOIDED,
                'voided_at' => now(),
                'voided_by_user_id' => $actor->id,
                'void_reason' => $reason,
            ]);

            $dueItem = RentDueItem::query()
                ->lockForUpdate()
                ->findOrFail($payment->rent_due_item_id);

            $this->refreshDueItem($dueItem);

            app(AuditService::class)->forModel(
                action: 'rent_payment.voided',
                model: $payment,
                oldValues: $oldValues,
                newValues: $payment->fresh()->toArray(),
                actor: $actor,
                request: request(),
            );
        });
    }

    private function refreshDueItem(RentDueItem $dueItem): void
    {
        $paid = (float) RentPayment::query()
            ->where('rent_due_item_id', $dueItem->id)
            ->where('status', RentPayment::STATUS_POSTED)
            ->sum('amount');

        $amount = (float) $dueItem->amount;

        $status = match (true) {
            $paid >= $amount => RentDueItem::STATUS_PAID,
            $paid > 0 => RentDueItem::STATUS_PARTIAL,
            $dueItem->due_date->isPast() => RentDueItem::STATUS_OVERDUE,
            default => RentDueItem::STATUS_DUE,
        };

        $dueItem->update([
            'paid_amount' => min($paid, $amount),
            'status' => $status,
        ]);
    }
}
