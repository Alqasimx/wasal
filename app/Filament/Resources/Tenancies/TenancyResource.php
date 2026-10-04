<?php

namespace App\Filament\Resources\Tenancies;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Tenancies\Pages\CreateTenancy;
use App\Filament\Resources\Tenancies\Pages\EditTenancy;
use App\Filament\Resources\Tenancies\Pages\ListTenancies;
use App\Filament\Resources\Tenancies\Schemas\TenancyForm;
use App\Filament\Resources\Tenancies\Tables\TenanciesTable;
use App\Models\Tenancy;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TenancyResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Tenancy::class;
    protected static string $viewPermission = 'tenancies.view';
    protected static string $managePermission = 'tenancies.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $navigationLabel = 'عقود الإيجار';
    protected static ?string $modelLabel = 'عقد إيجار';
    protected static ?string $pluralModelLabel = 'عقود الإيجار';
    protected static string|UnitEnum|null $navigationGroup = 'إدارة الأملاك';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return Filament::getCurrentPanel()?->getId() === 'property-management'
            ? 'الإيجارات والتحصيل'
            : 'إدارة الأملاك';
    }

    public static function form(Schema $schema): Schema
    {
        return TenancyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenanciesTable::configure($table);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenancies::route('/'),
            'create' => CreateTenancy::route('/create'),
            'edit' => EditTenancy::route('/{record}/edit'),
        ];
    }
}
