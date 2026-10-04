<?php

namespace App\Providers\Filament;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Resources\Banks\BankResource;
use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\Currencies\CurrencyResource;
use App\Filament\Resources\Districts\DistrictResource;
use App\Filament\Resources\Governorates\GovernorateResource;
use App\Filament\Resources\Neighborhoods\NeighborhoodResource;
use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\RecentAuditLogs;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('وصال — إدارة النظام')
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
                NavigationGroup::make()->label('إدارة النظام'),
                NavigationGroup::make()->label('إعدادات النظام'),
                NavigationGroup::make()->label('البيانات المرجعية'),
                NavigationGroup::make()->label('المواقع'),
                NavigationGroup::make()->label('المالية'),
            ])
            ->resources([
                UserResource::class,
                RoleResource::class,
                PermissionResource::class,
                SettingResource::class,
                CountryResource::class,
                GovernorateResource::class,
                CityResource::class,
                DistrictResource::class,
                NeighborhoodResource::class,
                CurrencyResource::class,
                BankResource::class,
                AuditLogResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                RecentAuditLogs::class,
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
