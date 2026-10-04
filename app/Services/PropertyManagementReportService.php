<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\OwnerSettlement;
use App\Models\PropertyDocument;
use App\Models\PropertyExpense;
use App\Models\PropertyManagementAgreement;
use App\Models\PropertyUnit;
use App\Models\RentDueItem;
use App\Models\RentPayment;
use App\Models\Tenancy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PropertyManagementReportService
{
    public function dashboard(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from ??= now()->startOfMonth();
        $to ??= now()->endOfMonth();

        $totalUnits = PropertyUnit::query()->currentlyManaged()->count();
        $occupiedUnits = PropertyUnit::query()->currentlyManaged()->occupied()->count();

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'kpis' => [
                'managed_properties' => PropertyManagementAgreement::query()
                    ->where('status', PropertyManagementAgreement::STATUS_ACTIVE)
                    ->whereDate('starts_at', '<=', today())
                    ->where(function ($query): void {
                        $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', today());
                    })
                    ->distinct('property_id')
                    ->count('property_id'),
                'total_units' => $totalUnits,
                'occupied_units' => $occupiedUnits,
                'vacant_units' => max(0, $totalUnits - $occupiedUnits),
                'occupancy_rate' => $totalUnits > 0
                    ? round(($occupiedUnits / $totalUnits) * 100, 1)
                    : 0,
                'active_tenancies' => Tenancy::query()
                    ->where('status', Tenancy::STATUS_ACTIVE)
                    ->count(),
                'overdue_dues' => RentDueItem::query()
                    ->whereIn('status', [
                        RentDueItem::STATUS_DUE,
                        RentDueItem::STATUS_PARTIAL,
                        RentDueItem::STATUS_OVERDUE,
                    ])
                    ->whereDate('due_date', '<', today())
                    ->whereColumn('paid_amount', '<', 'amount')
                    ->count(),
                'open_maintenance' => MaintenanceRequest::query()
                    ->whereNotIn('status', [
                        MaintenanceRequest::STATUS_COMPLETED,
                        MaintenanceRequest::STATUS_CANCELLED,
                    ])
                    ->count(),
                'expiring_documents' => PropertyDocument::query()
                    ->where('status', PropertyDocument::STATUS_ACTIVE)
                    ->whereNotNull('expires_at')
                    ->whereBetween('expires_at', [today(), today()->addDays(30)])
                    ->count(),
                'expiring_agreements' => PropertyManagementAgreement::query()
                    ->where('status', PropertyManagementAgreement::STATUS_ACTIVE)
                    ->whereNotNull('ends_at')
                    ->whereBetween('ends_at', [today(), today()->addDays(30)])
                    ->count(),
                'pending_owner_settlements' => OwnerSettlement::query()
                    ->whereIn('status', [
                        OwnerSettlement::STATUS_DRAFT,
                        OwnerSettlement::STATUS_APPROVED,
                    ])
                    ->count(),
            ],
            'collections' => $this->currencyTotals(
                RentPayment::query()
                    ->where('status', RentPayment::STATUS_POSTED)
                    ->whereBetween('paid_at', [
                        $from->copy()->startOfDay(),
                        $to->copy()->endOfDay(),
                    ])
            ),
            'expenses' => $this->currencyTotals(
                PropertyExpense::query()
                    ->whereBetween('incurred_at', [
                        $from->toDateString(),
                        $to->toDateString(),
                    ]),
                'amount'
            ),
            'outstanding' => $this->outstandingByCurrency(),
            'maintenance_by_status' => MaintenanceRequest::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->map(fn ($value): int => (int) $value)
                ->all(),
        ];
    }

    public function collectionsRows(Carbon $from, Carbon $to): Collection
    {
        return RentPayment::query()
            ->with(['tenancy.tenant', 'tenancy.unit.property', 'currency'])
            ->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()])
            ->orderBy('paid_at')
            ->get();
    }

    public function arrearsRows(): Collection
    {
        return RentDueItem::query()
            ->with(['tenancy.tenant', 'tenancy.unit.property', 'currency'])
            ->whereIn('status', [
                RentDueItem::STATUS_DUE,
                RentDueItem::STATUS_PARTIAL,
                RentDueItem::STATUS_OVERDUE,
            ])
            ->whereDate('due_date', '<=', today())
            ->whereColumn('paid_amount', '<', 'amount')
            ->orderBy('due_date')
            ->get();
    }

    public function expensesRows(Carbon $from, Carbon $to): Collection
    {
        return PropertyExpense::query()
            ->with(['property', 'unit', 'currency', 'vendor'])
            ->whereBetween('incurred_at', [$from->toDateString(), $to->toDateString()])
            ->orderBy('incurred_at')
            ->get();
    }

    public function maintenanceRows(Carbon $from, Carbon $to): Collection
    {
        return MaintenanceRequest::query()
            ->with(['property', 'unit', 'service', 'vendor', 'currency'])
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->orderBy('created_at')
            ->get();
    }

    public function settlementRows(Carbon $from, Carbon $to): Collection
    {
        return OwnerSettlement::query()
            ->with(['property', 'owner.user', 'currency'])
            ->whereBetween('period_end', [$from->toDateString(), $to->toDateString()])
            ->orderBy('period_end')
            ->get();
    }

    private function currencyTotals($query, string $amountColumn = 'amount'): array
    {
        return $query
            ->selectRaw("currency_id, SUM({$amountColumn}) as total")
            ->with('currency')
            ->groupBy('currency_id')
            ->get()
            ->map(fn ($row): array => [
                'currency' => $row->currency?->code ?? '—',
                'total' => (float) $row->total,
            ])
            ->values()
            ->all();
    }

    private function outstandingByCurrency(): array
    {
        return RentDueItem::query()
            ->selectRaw('currency_id, SUM(amount - paid_amount) as total')
            ->with('currency')
            ->whereIn('status', [
                RentDueItem::STATUS_DUE,
                RentDueItem::STATUS_PARTIAL,
                RentDueItem::STATUS_OVERDUE,
            ])
            ->whereColumn('paid_amount', '<', 'amount')
            ->groupBy('currency_id')
            ->get()
            ->map(fn (RentDueItem $row): array => [
                'currency' => $row->currency?->code ?? '—',
                'total' => (float) $row->total,
            ])
            ->values()
            ->all();
    }
}
