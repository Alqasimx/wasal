<?php

namespace App\Filament\Resources\Properties;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Properties\Pages\CreateProperty;
use App\Filament\Resources\Properties\Pages\EditProperty;
use App\Filament\Resources\Properties\Pages\ListProperties;
use App\Filament\Resources\Properties\Schemas\PropertyForm;
use App\Filament\Resources\Properties\Tables\PropertiesTable;
use App\Models\Property;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PropertyResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Property::class;
    protected static string $viewPermission = 'properties.view';
    protected static string $managePermission = 'properties.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;
    protected static ?string $recordTitleAttribute = 'title_ar';
    protected static ?string $navigationLabel = 'العقارات';
    protected static ?string $modelLabel = 'عقار';
    protected static ?string $pluralModelLabel = 'العقارات';
    protected static string|UnitEnum|null $navigationGroup = 'العقارات';

    public static function form(Schema $schema): Schema { return PropertyForm::configure($schema); }
    public static function table(Table $table): Table { return PropertiesTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => ListProperties::route('/'),
            'create' => CreateProperty::route('/create'),
            'edit' => EditProperty::route('/{record}/edit'),
        ];
    }
}
