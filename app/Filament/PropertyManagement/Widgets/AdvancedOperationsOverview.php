<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Filament\PropertyManagement\Resources\PropertyDocuments\PropertyDocumentResource;
use App\Filament\PropertyManagement\Resources\PropertyInspections\PropertyInspectionResource;
use App\Filament\PropertyManagement\Resources\UtilityMeters\UtilityMeterResource;
use App\Models\PropertyDocument;
use App\Models\PropertyInspection;
use App\Models\UtilityMeter;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdvancedOperationsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 8;

    public static function canView(): bool
    {
        return auth()->user()?->can('property_inspections.view') ?? false;
    }

    protected function getStats(): array
    {
        $upcomingInspections = PropertyInspection::query()
            ->whereIn('status', [
                PropertyInspection::STATUS_SCHEDULED,
                PropertyInspection::STATUS_IN_PROGRESS,
            ])
            ->whereBetween('scheduled_at', [now()->subDay(), now()->addDays(7)])
            ->count();

        $activeMeters = UtilityMeter::query()
            ->where('is_active', true)
            ->count();

        $expiringDocuments = PropertyDocument::query()
            ->where('status', PropertyDocument::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [today(), today()->addDays(30)])
            ->count();

        return [
            Stat::make('معاينات خلال 7 أيام', $upcomingInspections)
                ->description('استلام وتسليم ومعاينات دورية')
                ->icon('heroicon-o-clipboard-document-check')
                ->url(PropertyInspectionResource::getUrl('index', panel: 'property-management')),

            Stat::make('عدادات نشطة', $activeMeters)
                ->description('كهرباء ومياه ومرافق أخرى')
                ->icon('heroicon-o-bolt')
                ->url(UtilityMeterResource::getUrl('index', panel: 'property-management')),

            Stat::make('مستندات تنتهي خلال 30 يومًا', $expiringDocuments)
                ->description('مستندات تحتاج تجديد أو متابعة')
                ->icon('heroicon-o-document-text')
                ->url(PropertyDocumentResource::getUrl('index', panel: 'property-management')),
        ];
    }
}
