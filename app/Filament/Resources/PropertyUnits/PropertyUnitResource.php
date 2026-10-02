<?php
namespace App\Filament\Resources\PropertyUnits;
use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\PropertyUnits\Pages\CreatePropertyUnit;
use App\Filament\Resources\PropertyUnits\Pages\EditPropertyUnit;
use App\Filament\Resources\PropertyUnits\Pages\ListPropertyUnits;
use App\Filament\Resources\PropertyUnits\Schemas\PropertyUnitForm;
use App\Filament\Resources\PropertyUnits\Tables\PropertyUnitsTable;
use App\Models\PropertyUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
class PropertyUnitResource extends Resource
{
    use HasResourcePermissions;
    protected static ?string $model = PropertyUnit::class;
    protected static string $viewPermission = 'property_units.view';
    protected static string $managePermission = 'property_units.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $navigationLabel = 'وحدات العقارات';
    protected static ?string $modelLabel = 'وحدة عقارية';
    protected static ?string $pluralModelLabel = 'وحدات العقارات';
    protected static string|UnitEnum|null $navigationGroup = 'العقارات';
    public static function form(Schema $schema): Schema { return PropertyUnitForm::configure($schema); }
    public static function table(Table $table): Table { return PropertyUnitsTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return ['index'=>ListPropertyUnits::route('/'),'create'=>CreatePropertyUnit::route('/create'),'edit'=>EditPropertyUnit::route('/{record}/edit')]; }
}
