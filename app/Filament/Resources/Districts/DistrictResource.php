<?php

namespace App\Filament\Resources\Districts;

use App\Filament\Resources\Districts\Pages\CreateDistrict;
use App\Filament\Resources\Districts\Pages\EditDistrict;
use App\Filament\Resources\Districts\Pages\ListDistricts;
use App\Filament\Resources\Districts\Schemas\DistrictForm;
use App\Filament\Resources\Districts\Tables\DistrictsTable;
use App\Models\District;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $navigationLabel = 'ط§ظ„ظ…ط¯ظٹط±ظٹط§طھ';

    protected static ?string $modelLabel = 'ظ…ط¯ظٹط±ظٹط©';

    protected static ?string $pluralModelLabel = 'ط§ظ„ظ…ط¯ظٹط±ظٹط§طھ';

    protected static string|UnitEnum|null $navigationGroup = 'ط§ظ„ظ…ظˆط§ظ‚ط¹';

    public static function form(Schema $schema): Schema
    {
        return DistrictForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DistrictsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDistricts::route('/'),
            'create' => CreateDistrict::route('/create'),
            'edit' => EditDistrict::route('/{record}/edit'),
        ];
    }
}
