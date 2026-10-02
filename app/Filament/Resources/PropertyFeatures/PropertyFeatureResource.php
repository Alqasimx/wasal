<?php

namespace App\Filament\Resources\PropertyFeatures;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\PropertyFeatures\Pages\CreatePropertyFeature;
use App\Filament\Resources\PropertyFeatures\Pages\EditPropertyFeature;
use App\Filament\Resources\PropertyFeatures\Pages\ListPropertyFeatures;
use App\Filament\Resources\PropertyFeatures\Schemas\PropertyFeatureForm;
use App\Filament\Resources\PropertyFeatures\Tables\PropertyFeaturesTable;
use App\Models\PropertyFeature;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PropertyFeatureResource extends Resource
{
    use HasResourcePermissions;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $model = PropertyFeature::class;

    protected static string $viewPermission = 'property_features.view';

    protected static string $managePermission = 'property_features.manage';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $navigationLabel = 'خصائص العقارات';

    protected static ?string $modelLabel = 'خاصية عقار';

    protected static ?string $pluralModelLabel = 'خصائص العقارات';

    protected static string|UnitEnum|null $navigationGroup = 'العقارات';

    public static function form(Schema $schema): Schema
    {
        return PropertyFeatureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertyFeaturesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyFeatures::route('/'),
            'create' => CreatePropertyFeature::route('/create'),
            'edit' => EditPropertyFeature::route('/{record}/edit'),
        ];
    }
}
