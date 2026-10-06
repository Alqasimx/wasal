<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Filament\Resources\PropertyUnits\PropertyUnitResource;
use App\Filament\Resources\Tenancies\TenancyResource;
use App\Models\PropertyUnit;
use App\Models\Tenancy;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OccupancyOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return auth()->user()?->can('property_units.view') ?? false;
    }

    protected function getStats(): array
    {
        $total = PropertyUnit::query()
            ->currentlyManaged()
            ->count();

        $occupied = PropertyUnit::query()
            ->currentlyManaged()
            ->occupied()
            ->count();

        $vacant = PropertyUnit::query()
            ->currentlyManaged()
            ->vacant()
            ->where('status', PropertyUnit::STATUS_AVAILABLE)
            ->count();

        $maintenance = PropertyUnit::query()
            ->currentlyManaged()
            ->where('status', PropertyUnit::STATUS_MAINTENANCE)
            ->count();

        $occupancyRate = $total > 0
            ? round(($occupied / $total) * 100, 1)
            : 0;

        $expiringSoon = Tenancy::query()
            ->where('status', Tenancy::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [
                today(),
                today()->copy()->addDays(30),
            ])
            ->whereHas('unit', fn ($query) => $query->currentlyManaged())
            ->count();

        return [
            Stat::make('إجمالي الوحدات المُدارة', $total)
                ->description('الوحدات التابعة لاتفاقات إدارة نشطة')
                ->icon('heroicon-o-building-office')
                ->url(PropertyUnitResource::getUrl('index', panel: 'property-management')),

            Stat::make('الوحدات المشغولة', $occupied)
                ->description('لديها عقد نشط حاليًا')
                ->icon('heroicon-o-key')
                ->url(PropertyUnitResource::getUrl('index', panel: 'property-management')),

            Stat::make('الوحدات الشاغرة', $vacant)
                ->description('متاحة ولا يوجد عليها عقد نشط')
                ->icon('heroicon-o-home-modern')
                ->url(PropertyUnitResource::getUrl('index', panel: 'property-management')),

            Stat::make('تحت الصيانة', $maintenance)
                ->description('وحدات غير جاهزة للتشغيل حاليًا')
                ->icon('heroicon-o-wrench-screwdriver')
                ->url(PropertyUnitResource::getUrl('index', panel: 'property-management')),

            Stat::make('نسبة الإشغال', $occupancyRate.'%')
                ->description($occupied.' من '.$total.' وحدة')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('عقود تنتهي خلال 30 يومًا', $expiringSoon)
                ->description('تحتاج متابعة تجديد أو إخلاء')
                ->icon('heroicon-o-calendar-days')
                ->url(TenancyResource::getUrl('index', panel: 'property-management')),
        ];
    }
}
