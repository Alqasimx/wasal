<?php

namespace App\Filament\Resources\Tenants;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Resources\Tenants\Pages\EditTenant;
use App\Filament\Resources\Tenants\Pages\ListTenants;
use App\Filament\Resources\Tenants\Schemas\TenantForm;
use App\Filament\Resources\Tenants\Tables\TenantsTable;
use App\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TenantResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Tenant::class;
    protected static string $viewPermission = 'tenants.view';
    protected static string $managePermission = 'tenants.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'المستأجرون';
    protected static ?string $modelLabel = 'مستأجر';
    protected static ?string $pluralModelLabel = 'المستأجرون';
    protected static string|UnitEnum|null $navigationGroup = 'إدارة الأملاك';

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->can(static::$managePermission) ?? false)
            && ! $record->tenancies()->exists();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }
}
