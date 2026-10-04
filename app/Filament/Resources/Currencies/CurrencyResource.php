<?php

namespace App\Filament\Resources\Currencies;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Currencies\Pages\CreateCurrency;
use App\Filament\Resources\Currencies\Pages\EditCurrency;
use App\Filament\Resources\Currencies\Pages\ListCurrencies;
use App\Filament\Resources\Currencies\Schemas\CurrencyForm;
use App\Filament\Resources\Currencies\Tables\CurrenciesTable;
use App\Models\Currency;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CurrencyResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Currency::class;
    protected static string $viewPermission = 'currencies.view';
    protected static string $managePermission = 'currencies.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = 'name_ar';
    protected static ?string $navigationLabel = 'العملات';
    protected static ?string $modelLabel = 'عملة';
    protected static ?string $pluralModelLabel = 'العملات';
    protected static string|UnitEnum|null $navigationGroup = 'البيانات المرجعية';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return Filament::getCurrentPanel()?->getId() === 'property-management'
            ? 'التقارير والإعدادات'
            : 'البيانات المرجعية';
    }

    public static function form(Schema $schema): Schema
    {
        return CurrencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrenciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}
