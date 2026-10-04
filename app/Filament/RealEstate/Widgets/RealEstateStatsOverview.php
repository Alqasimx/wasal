<?php

namespace App\Filament\RealEstate\Widgets;

use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Resources\PropertyListings\PropertyListingResource;
use App\Filament\Resources\PropertyRequests\PropertyRequestResource;
use App\Models\Property;
use App\Models\PropertyListing;
use App\Models\PropertyRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RealEstateStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = [];
        $user = auth()->user();

        if ($user?->can('properties.view')) {
            $stats[] = Stat::make('العقارات', Property::query()->count())
                ->description('إجمالي العقارات المسجلة')
                ->icon('heroicon-o-building-office-2')
                ->url(PropertyResource::getUrl('index', panel: 'real-estate'));
        }

        if ($user?->can('property_listings.view')) {
            $stats[] = Stat::make(
                'الإعلانات المنشورة',
                PropertyListing::query()
                    ->where('status', PropertyListing::STATUS_PUBLISHED)
                    ->count()
            )
                ->description('الإعلانات النشطة حاليًا')
                ->icon('heroicon-o-megaphone')
                ->url(PropertyListingResource::getUrl('index', panel: 'real-estate'));
        }

        if ($user?->can('property_requests.view')) {
            $stats[] = Stat::make(
                'طلبات العقار',
                PropertyRequest::query()
                    ->whereNotIn('status', [
                        PropertyRequest::STATUS_COMPLETED,
                        PropertyRequest::STATUS_CANCELLED,
                    ])
                    ->count()
            )
                ->description('طلبات تحتاج متابعة')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(PropertyRequestResource::getUrl('index', panel: 'real-estate'));
        }

        return $stats;
    }
}
