<?php

namespace App\Filament\Resources\PropertyOwners;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\PropertyOwners\Pages\CreatePropertyOwner;
use App\Filament\Resources\PropertyOwners\Pages\EditPropertyOwner;
use App\Filament\Resources\PropertyOwners\Pages\ListPropertyOwners;
use App\Filament\Resources\PropertyOwners\Schemas\PropertyOwnerForm;
use App\Filament\Resources\PropertyOwners\Tables\PropertyOwnersTable;
use App\Models\PropertyOwner;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PropertyOwnerResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = PropertyOwner::class;
    protected static string $viewPermission = 'property_owners.view';
    protected static string $managePermission = 'property_owners.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'ملاك العقارات';
    protected static ?string $modelLabel = 'مالك عقار';
    protected static ?string $pluralModelLabel = 'ملاك العقارات';
    protected static string|UnitEnum|null $navigationGroup = 'العقارات';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return Filament::getCurrentPanel()?->getId() === 'property-management'
            ? 'الملاك والتسويات'
            : 'العقارات';
    }

    public static function form(Schema $schema): Schema
    {
        return PropertyOwnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertyOwnersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyOwners::route('/'),
            'create' => CreatePropertyOwner::route('/create'),
            'edit' => EditPropertyOwner::route('/{record}/edit'),
        ];
    }
}
