<?php

namespace App\Providers\Filament;

use App\Filament\RealEstate\Widgets\RealEstateStatsOverview;
use App\Filament\Widgets\PanelSwitcher;
use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\Currencies\CurrencyResource;
use App\Filament\Resources\Districts\DistrictResource;
use App\Filament\Resources\Governorates\GovernorateResource;
use App\Filament\Resources\Neighborhoods\NeighborhoodResource;
use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Resources\PropertyFeatures\PropertyFeatureResource;
use App\Filament\Resources\PropertyListings\PropertyListingResource;
use App\Filament\Resources\PropertyOwners\PropertyOwnerResource;
use App\Filament\Resources\PropertyRequests\PropertyRequestResource;
use App\Filament\Resources\PropertyTypes\PropertyTypeResource;
use App\Filament\Resources\PropertyUnits\PropertyUnitResource;
use App\Filament\Resources\Tasks\TaskResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class RealEstatePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('real-estate')
            ->path('real-estate')
            ->login()
            ->brandName('وصال — العقارات')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): \Illuminate\Contracts\View\View => view('filament.admin.styles'),
            )
            ->renderHook(
                PanelsRenderHook::PAGE_START,
                fn (): \Illuminate\Contracts\View\View => view('filament.components.panel-context-bar'),
            )
            ->colors([
                'primary' => Color::hex('#D4AF37'),
                'gray' => Color::Zinc,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Sky,
            ])
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()->label('العقارات'),
                NavigationGroup::make()->label('المواقع'),
                NavigationGroup::make()->label('البيانات المرجعية'),
            ])
            ->resources([
                PropertyResource::class,
                PropertyUnitResource::class,
                PropertyOwnerResource::class,
                PropertyListingResource::class,
                PropertyRequestResource::class,
                TaskResource::class,
                CountryResource::class,
                GovernorateResource::class,
                CityResource::class,
                DistrictResource::class,
                NeighborhoodResource::class,
                PropertyTypeResource::class,
                PropertyFeatureResource::class,
                CurrencyResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                PanelSwitcher::class,
                RealEstateStatsOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
