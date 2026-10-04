<?php

namespace App\Providers\Filament;

use App\Filament\PropertyManagement\Widgets\OccupancyOverview;
use App\Filament\PropertyManagement\Widgets\PropertyManagementStatsOverview;
use App\Filament\PropertyManagement\Widgets\PropertyManagementTasks;
use App\Filament\Resources\Currencies\CurrencyResource;
use App\Filament\Resources\PropertyOwners\PropertyOwnerResource;
use App\Filament\Resources\PropertyUnits\PropertyUnitResource;
use App\Filament\Resources\RentDueItems\RentDueItemResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Filament\Resources\Tenancies\TenancyResource;
use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Widgets\PanelSwitcher;
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

class PropertyManagementPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('property-management')
            ->path('property-management')
            ->login()
            ->brandName('وصال — إدارة الأملاك')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): \Illuminate\Contracts\View\View => view('filament.admin.styles'),
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
                NavigationGroup::make()->label('نظرة عامة'),
                NavigationGroup::make()->label('الأصول والإشغال'),
                NavigationGroup::make()->label('الإيجارات والتحصيل'),
                NavigationGroup::make()->label('الخدمات والصيانة'),
                NavigationGroup::make()->label('الملاك والتسويات'),
                NavigationGroup::make()->label('التقارير والإعدادات'),
            ])
            ->resources([
                PropertyUnitResource::class,
                PropertyOwnerResource::class,
                TenantResource::class,
                TenancyResource::class,
                RentDueItemResource::class,
                TaskResource::class,
                CurrencyResource::class,
            ])
            ->discoverResources(
                in: app_path('Filament/PropertyManagement/Resources'),
                for: 'App\\Filament\\PropertyManagement\\Resources',
            )
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                PanelSwitcher::class,
                PropertyManagementStatsOverview::class,
                OccupancyOverview::class,
                PropertyManagementTasks::class,
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
