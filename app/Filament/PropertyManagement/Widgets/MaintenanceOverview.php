<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Filament\PropertyManagement\Resources\MaintenanceRequests\MaintenanceRequestResource;
use App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\PropertyServiceScheduleResource;
use App\Models\MaintenanceRequest;
use App\Models\PropertyServiceSchedule;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MaintenanceOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return auth()->user()?->can('maintenance_requests.view') ?? false;
    }

    protected function getStats(): array
    {
        $open = MaintenanceRequest::query()
            ->whereNotIn('status', [
                MaintenanceRequest::STATUS_COMPLETED,
                MaintenanceRequest::STATUS_CANCELLED,
            ])
            ->count();

        $urgent = MaintenanceRequest::query()
            ->where('priority', MaintenanceRequest::PRIORITY_URGENT)
            ->whereNotIn('status', [
                MaintenanceRequest::STATUS_COMPLETED,
                MaintenanceRequest::STATUS_CANCELLED,
            ])
            ->count();

        $inProgress = MaintenanceRequest::query()
            ->where('status', MaintenanceRequest::STATUS_IN_PROGRESS)
            ->count();

        $upcomingServices = PropertyServiceSchedule::query()
            ->where('is_active', true)
            ->whereBetween('next_due_at', [now(), now()->copy()->addDays(7)])
            ->count();

        return [
            Stat::make('طلبات الصيانة المفتوحة', $open)
                ->description('كل الأعمال التي لم تغلق بعد')
                ->icon('heroicon-o-wrench-screwdriver')
                ->url(MaintenanceRequestResource::getUrl('index', panel: 'property-management')),

            Stat::make('طلبات عاجلة', $urgent)
                ->description('تحتاج أولوية في التنفيذ')
                ->icon('heroicon-o-exclamation-triangle')
                ->url(MaintenanceRequestResource::getUrl('index', panel: 'property-management')),

            Stat::make('قيد التنفيذ', $inProgress)
                ->description('أعمال صيانة جارية الآن')
                ->icon('heroicon-o-cog-6-tooth')
                ->url(MaintenanceRequestResource::getUrl('index', panel: 'property-management')),

            Stat::make('خدمات خلال 7 أيام', $upcomingServices)
                ->description('خدمات دورية قادمة')
                ->icon('heroicon-o-calendar-days')
                ->url(PropertyServiceScheduleResource::getUrl('index', panel: 'property-management')),
        ];
    }
}
