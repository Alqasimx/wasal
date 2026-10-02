<?php

namespace App\Filament\Resources\Countries;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Countries\Pages\CreateCountry;
use App\Filament\Resources\Countries\Pages\EditCountry;
use App\Filament\Resources\Countries\Pages\ListCountries;
use App\Filament\Resources\Countries\Schemas\CountryForm;
use App\Filament\Resources\Countries\Tables\CountriesTable;
use App\Models\Country;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CountryResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Country::class;

    protected static string $viewPermission = 'geography.view';

    protected static string $managePermission = 'geography.manage';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $navigationLabel = 'الدول';

    protected static ?string $modelLabel = 'دولة';

    protected static ?string $pluralModelLabel = 'الدول';

    protected static string|UnitEnum|null $navigationGroup =
        'المواقع';


    public static function form(Schema $schema): Schema
    {
        return CountryForm::configure($schema);
    }


    public static function table(Table $table): Table
    {
        return CountriesTable::configure($table);
    }


    public static function getRelations(): array
    {
        return [];
    }


    public static function getPages(): array
    {
        return [
            'index' => ListCountries::route('/'),
            'create' => CreateCountry::route('/create'),
            'edit' => EditCountry::route('/{record}/edit'),
        ];
    }
}
