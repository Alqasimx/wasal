<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Resources\Banks\BankResource;
use App\Filament\Resources\Currencies\CurrencyResource;
use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Resources\PropertyListings\PropertyListingResource;
use App\Filament\Resources\PropertyRequests\PropertyRequestResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\Property;
use App\Models\PropertyListing;
use App\Models\PropertyRequest;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WasalStatsOverview extends StatsOverviewWidget
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
                ->color('primary')
                ->url(PropertyResource::getUrl('index'));
        }

        if ($user?->can('property_listings.view')) {
            $stats[] = Stat::make('الإعلانات المنشورة', PropertyListing::query()->where('status', PropertyListing::STATUS_PUBLISHED)->count())
                ->description('عروض ظاهرة للعملاء')
                ->icon('heroicon-o-megaphone')
                ->color('success')
                ->url(PropertyListingResource::getUrl('index'));
        }

        if ($user?->can('property_requests.view')) {
            $stats[] = Stat::make('طلبات العقار', PropertyRequest::query()->whereNotIn('status', [PropertyRequest::STATUS_COMPLETED, PropertyRequest::STATUS_CANCELLED])->count())
                ->description('طلبات تحتاج متابعة')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning')
                ->url(PropertyRequestResource::getUrl('index'));
        }

        if ($user?->can('tasks.view')) {
            $stats[] = Stat::make('المهام المفتوحة', Task::query()->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])->count())
                ->description('مهام يومية وأسبوعية')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->url(TaskResource::getUrl('index'));
        }

        if ($user?->can('users.view')) {
            $stats[] = Stat::make(
                'المستخدمون',
                User::query()->count()
            )
                ->description('إجمالي حسابات وصال')
                ->icon('heroicon-o-users')
                ->url(
                    UserResource::getUrl('index')
                );
        }

        if ($user?->can('roles.view')) {
            $stats[] = Stat::make(
                'الأدوار',
                Role::query()->count()
            )
                ->description('أدوار وصلاحيات النظام')
                ->icon('heroicon-o-shield-check')
                ->url(
                    RoleResource::getUrl('index')
                );
        }

        if ($user?->can('banks.view')) {
            $stats[] = Stat::make(
                'الحسابات البنكية',
                Bank::query()
                    ->where('is_active', true)
                    ->count()
            )
                ->description('الحسابات البنكية النشطة')
                ->icon('heroicon-o-banknotes')
                ->url(
                    BankResource::getUrl('index')
                );
        }

        if ($user?->can('currencies.view')) {
            $stats[] = Stat::make(
                'العملات',
                Currency::query()
                    ->where('is_active', true)
                    ->count()
            )
                ->description('العملات النشطة')
                ->icon('heroicon-o-currency-dollar')
                ->url(
                    CurrencyResource::getUrl('index')
                );
        }

        if (
            $user?->can('audit_logs.view') ||
            $user?->can('audit_logs.financial_view')
        ) {
            $auditQuery = AuditLog::query();

            if (
                ! $user->can('audit_logs.view') &&
                $user->can('audit_logs.financial_view')
            ) {
                $auditQuery->whereIn(
                    'entity_type',
                    [
                        Bank::class,
                        Currency::class,
                    ]
                );
            }

            $stats[] = Stat::make(
                'سجل التدقيق',
                $auditQuery->count()
            )
                ->description('إجمالي العمليات المسموح بعرضها')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(
                    AuditLogResource::getUrl('index')
                );
        }

        return $stats;
    }
}
