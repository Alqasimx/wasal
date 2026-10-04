<?php

namespace App\Services;

use App\Models\OwnerSettlement;
use App\Models\PropertyExpense;
use App\Models\PropertyManagementAgreement;
use App\Models\RentPayment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OwnerSettlementService
{
    public function generate(
        PropertyManagementAgreement $agreement,
        Carbon $periodStart,
        Carbon $periodEnd,
        int $currencyId,
        User $actor,
        ?string $notes = null,
    ): OwnerSettlement {
        if ($periodEnd->lt($periodStart)) {
            throw ValidationException::withMessages([
                'period_end' => 'نهاية الفترة يجب أن تكون بعد بدايتها.',
            ]);
        }

        if (! $agreement->property_owner_id) {
            throw ValidationException::withMessages([
                'property_management_agreement_id' => 'اتفاق الإدارة لا يحتوي على مالك مرتبط.',
            ]);
        }

        return DB::transaction(function () use (
            $agreement,
            $periodStart,
            $periodEnd,
            $currencyId,
            $actor,
            $notes,
        ): OwnerSettlement {
            $existing = OwnerSettlement::query()
                ->where('property_management_agreement_id', $agreement->id)
                ->whereDate('period_start', $periodStart)
                ->whereDate('period_end', $periodEnd)
                ->where('currency_id', $currencyId)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->status !== OwnerSettlement::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'period_start' => 'توجد تسوية معتمدة أو مدفوعة لهذه الفترة والعملة.',
                ]);
            }

            $grossCollections = (float) RentPayment::query()
                ->where('status', RentPayment::STATUS_POSTED)
                ->where('currency_id', $currencyId)
                ->whereBetween('paid_at', [
                    $periodStart->copy()->startOfDay(),
                    $periodEnd->copy()->endOfDay(),
                ])
                ->whereHas('tenancy.unit', fn ($query) =>
                    $query->where('property_id', $agreement->property_id))
                ->sum('amount');

            $ownerExpenses = (float) PropertyExpense::query()
                ->where('property_id', $agreement->property_id)
                ->where('currency_id', $currencyId)
                ->where('cost_bearer', 'owner')
                ->whereBetween('incurred_at', [
                    $periodStart->toDateString(),
                    $periodEnd->toDateString(),
                ])
                ->sum('amount');

            $managementFee = $this->managementFee(
                agreement: $agreement,
                grossCollections: $grossCollections,
                currencyId: $currencyId,
            );

            $values = [
                'property_management_agreement_id' => $agreement->id,
                'property_owner_id' => $agreement->property_owner_id,
                'property_id' => $agreement->property_id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'currency_id' => $currencyId,
                'gross_collections' => $grossCollections,
                'owner_expenses' => $ownerExpenses,
                'management_fee' => $managementFee,
                'net_payable' => $grossCollections - $ownerExpenses - $managementFee,
                'status' => OwnerSettlement::STATUS_DRAFT,
                'created_by_user_id' => $actor->id,
                'notes' => $notes,
            ];

            if ($existing) {
                $existing->update($values);
                $settlement = $existing->fresh();
            } else {
                $settlement = OwnerSettlement::create($values);
            }

            app(AuditService::class)->forModel(
                action: 'owner_settlement.generated',
                model: $settlement,
                newValues: $settlement->toArray(),
                actor: $actor,
                request: request(),
            );

            return $settlement;
        });
    }

    public function approve(OwnerSettlement $settlement, User $actor): void
    {
        if ($settlement->status !== OwnerSettlement::STATUS_DRAFT) {
            return;
        }

        $oldValues = $settlement->toArray();

        $settlement->update([
            'status' => OwnerSettlement::STATUS_APPROVED,
            'approved_by_user_id' => $actor->id,
            'approved_at' => now(),
        ]);

        app(AuditService::class)->forModel(
            action: 'owner_settlement.approved',
            model: $settlement,
            oldValues: $oldValues,
            newValues: $settlement->fresh()->toArray(),
            actor: $actor,
            request: request(),
        );
    }

    public function markPaid(
        OwnerSettlement $settlement,
        User $actor,
        ?string $paymentReference = null,
    ): void {
        if ($settlement->status !== OwnerSettlement::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'payment_reference' => 'يجب اعتماد التسوية قبل تسجيل سدادها.',
            ]);
        }

        $oldValues = $settlement->toArray();

        $settlement->update([
            'status' => OwnerSettlement::STATUS_PAID,
            'paid_by_user_id' => $actor->id,
            'paid_at' => now(),
            'payment_reference' => $paymentReference,
        ]);

        app(AuditService::class)->forModel(
            action: 'owner_settlement.paid',
            model: $settlement,
            oldValues: $oldValues,
            newValues: $settlement->fresh()->toArray(),
            actor: $actor,
            request: request(),
        );
    }

    private function managementFee(
        PropertyManagementAgreement $agreement,
        float $grossCollections,
        int $currencyId,
    ): float {
        if ($agreement->management_fee_type === PropertyManagementAgreement::FEE_PERCENTAGE) {
            return round(
                $grossCollections * ((float) $agreement->management_fee_value / 100),
                2,
            );
        }

        if ($agreement->currency_id && (int) $agreement->currency_id !== $currencyId) {
            throw ValidationException::withMessages([
                'currency_id' => 'عملة التسوية يجب أن تطابق عملة رسوم الإدارة الثابتة.',
            ]);
        }

        return (float) $agreement->management_fee_value;
    }
}
