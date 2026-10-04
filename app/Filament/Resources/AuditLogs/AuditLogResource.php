<?php

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Filament\Resources\AuditLogs\Pages\ViewAuditLog;
use App\Filament\Resources\AuditLogs\Schemas\AuditLogInfolist;
use App\Filament\Resources\AuditLogs\Tables\AuditLogsTable;
use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\PropertyExpense;
use App\Models\RentDueItem;
use App\Models\RentPayment;
use App\Models\Tenancy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'action';

    protected static ?string $navigationLabel = 'سجل التدقيق';

    protected static ?string $modelLabel = 'سجل تدقيق';

    protected static ?string $pluralModelLabel = 'سجل التدقيق';

    protected static string|UnitEnum|null $navigationGroup =
        'إدارة النظام';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('audit_logs.view')
            || $user->can('audit_logs.financial_view');
    }

    public static function canView(Model $record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->can('audit_logs.view')) {
            return true;
        }

        if (! $user->can('audit_logs.financial_view')) {
            return false;
        }

        return in_array(
            $record->entity_type,
            self::financialEntityTypes(),
            true
        );
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->can('audit_logs.view')) {
            return $query;
        }

        if ($user->can('audit_logs.financial_view')) {
            return $query->whereIn(
                'entity_type',
                self::financialEntityTypes()
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function canRestore(Model $record): bool
    {
        return false;
    }

    public static function canRestoreAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AuditLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
            'view' => ViewAuditLog::route('/{record}'),
        ];
    }

    private static function financialEntityTypes(): array
    {
        return [
            Bank::class,
            Currency::class,
            Tenancy::class,
            RentDueItem::class,
            RentPayment::class,
            PropertyExpense::class,
        ];
    }
}
