<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Filament\PropertyManagement\Resources\OwnerSettlements\OwnerSettlementResource;
use App\Filament\PropertyManagement\Resources\PropertyExpenses\PropertyExpenseResource;
use App\Models\OwnerSettlement;
use App\Models\PropertyExpense;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OwnerFinanceOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 7;

    public static function canView(): bool
    {
        return auth()->user()?->can('owner_settlements.view') ?? false;
    }

    protected function getStats(): array
    {
        $draft = OwnerSettlement::query()
            ->where('status', OwnerSettlement::STATUS_DRAFT)
            ->count();

        $approved = OwnerSettlement::query()
            ->where('status', OwnerSettlement::STATUS_APPROVED)
            ->count();

        $unpaidExpenses = PropertyExpense::query()
            ->whereIn('payment_status', [
                PropertyExpense::STATUS_UNPAID,
                PropertyExpense::STATUS_PARTIAL,
            ])
            ->count();

        return [
            Stat::make('تسويات تنتظر الاعتماد', $draft)
                ->description('راجع التحصيلات والمصروفات ورسوم وصال')
                ->icon('heroicon-o-document-currency-dollar')
                ->url(OwnerSettlementResource::getUrl('index', panel: 'property-management')),

            Stat::make('تسويات معتمدة غير مدفوعة', $approved)
                ->description('جاهزة لتسجيل السداد')
                ->icon('heroicon-o-banknotes')
                ->url(OwnerSettlementResource::getUrl('index', panel: 'property-management')),

            Stat::make('مصروفات غير مكتملة السداد', $unpaidExpenses)
                ->description('مصروفات تحتاج متابعة مالية')
                ->icon('heroicon-o-receipt-percent')
                ->url(PropertyExpenseResource::getUrl('index', panel: 'property-management')),
        ];
    }
}
