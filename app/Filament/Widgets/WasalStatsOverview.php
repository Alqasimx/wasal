<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Resources\Banks\BankResource;
use App\Filament\Resources\Currencies\CurrencyResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\Role;
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