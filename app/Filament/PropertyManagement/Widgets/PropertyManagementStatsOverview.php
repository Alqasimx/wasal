<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Filament\PropertyManagement\Resources\MaintenanceRequests\MaintenanceRequestResource;
use App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\PropertyManagementAgreementResource;
use App\Filament\PropertyManagement\Resources\RentPayments\RentPaymentResource;
use App\Filament\Resources\RentDueItems\RentDueItemResource;
use App\Filament\Resources\Tenancies\TenancyResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Models\MaintenanceRequest;
use App\Models\PropertyManagementAgreement;
use App\Models\RentDueItem;
use App\Models\RentPayment;
use App\Models\Tenancy;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PropertyManagementStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = [];
        $user = auth()->user();

        if ($user?->can('property_management_agreements.view')) {
            $stats[] = Stat::make(
                'العقارات المُدارة',
                PropertyManagementAgreement::query()
                    ->where('status', PropertyManagementAgreement::STATUS_ACTIVE)
                    ->distinct('property_id')
                    ->count('property_id')
            )
                ->description('عقارات تحت إدارة وصال حاليًا')
                ->icon('heroicon-o-building-office-2')
                ->url(PropertyManagementAgreementResource::getUrl('index', panel: 'property-management'));
        }

        if ($user?->can('tenancies.view')) {
            $stats[] = Stat::make(
                'العقود النشطة',
                Tenancy::query()
                    ->where('status', Tenancy::STATUS_ACTIVE)
                    ->count()
            )
                ->description('عقود إيجار جارية')
                ->icon('heroicon-o-building-office')
                ->url(TenancyResource::getUrl('index', panel: 'property-management'));
        }

        if ($user?->can('tenants.view')) {
            $stats[] = Stat::make('المستأجرون', Tenant::query()->count())
                ->description('إجمالي المستأجرين المسجلين')
                ->icon('heroicon-o-users')
                ->url(TenantResource::getUrl('index', panel: 'property-management'));
        }

        if ($user?->can('rent_due_items.view')) {
            $stats[] = Stat::make(
                'الاستحقاقات المتأخرة',
                RentDueItem::query()
                    ->whereIn('status', [
                        RentDueItem::STATUS_DUE,
                        RentDueItem::STATUS_PARTIAL,
                        RentDueItem::STATUS_OVERDUE,
                    ])
                    ->whereDate('due_date', '<', today())
                    ->whereColumn('paid_amount', '<', 'amount')
                    ->count()
            )
                ->description('استحقاقات تحتاج متابعة')
                ->icon('heroicon-o-banknotes')
                ->url(RentDueItemResource::getUrl('index', panel: 'property-management'));
        }

        if ($user?->can('rent_payments.view')) {
            $monthlyCollections = RentPayment::query()
                ->selectRaw('currency_id, SUM(amount) as total')
                ->with('currency')
                ->where('status', RentPayment::STATUS_POSTED)
                ->whereBetween('paid_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ])
                ->groupBy('currency_id')
                ->get()
                ->map(fn (RentPayment $payment): string =>
                    number_format((float) $payment->total, 2).' '.($payment->currency?->code ?? ''))
                ->join(' • ');

            $stats[] = Stat::make(
                'تحصيلات هذا الشهر',
                $monthlyCollections !== '' ? $monthlyCollections : '0'
            )
                ->description('معروضة حسب كل عملة بدون جمع العملات المختلفة')
                ->icon('heroicon-o-banknotes')
                ->url(RentPaymentResource::getUrl('index', panel: 'property-management'));
        }

        if ($user?->can('maintenance_requests.view')) {
            $stats[] = Stat::make(
                'الصيانة المفتوحة',
                MaintenanceRequest::query()
                    ->whereNotIn('status', [
                        MaintenanceRequest::STATUS_COMPLETED,
                        MaintenanceRequest::STATUS_CANCELLED,
                    ])
                    ->count()
            )
                ->description('طلبات صيانة قيد المتابعة')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(MaintenanceRequestResource::getUrl('index', panel: 'property-management'));
        }

        return $stats;
    }
}
