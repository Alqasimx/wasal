<?php

namespace App\Filament\Widgets;

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
        return [
            Stat::make(
                'المستخدمون',
                User::query()->count()
            )
                ->description('إجمالي حسابات وصال')
                ->icon('heroicon-o-users'),

            Stat::make(
                'الأدوار',
                Role::query()->count()
            )
                ->description('أدوار وصلاحيات النظام')
                ->icon('heroicon-o-shield-check'),

            Stat::make(
                'الحسابات البنكية',
                Bank::query()
                    ->where('is_active', true)
                    ->count()
            )
                ->description('الحسابات البنكية النشطة')
                ->icon('heroicon-o-banknotes'),

            Stat::make(
                'العملات',
                Currency::query()
                    ->where('is_active', true)
                    ->count()
            )
                ->description('العملات النشطة')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make(
                'سجل التدقيق',
                AuditLog::query()->count()
            )
                ->description('إجمالي العمليات المسجلة')
                ->icon('heroicon-o-clipboard-document-list'),
        ];
    }
}