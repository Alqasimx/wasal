<?php

namespace App\Filament\Resources\Neighborhoods;

use App\Filament\Resources\Neighborhoods\Pages\CreateNeighborhood;
use App\Filament\Resources\Neighborhoods\Pages\EditNeighborhood;
use App\Filament\Resources\Neighborhoods\Pages\ListNeighborhoods;
use App\Filament\Resources\Neighborhoods\Schemas\NeighborhoodForm;
use App\Filament\Resources\Neighborhoods\Tables\NeighborhoodsTable;
use App\Models\Neighborhood;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NeighborhoodResource extends Resource
{
    protected static ?string $model = Neighborhood::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $navigationLabel = 'ط§ظ„ط£ط­ظٹط§ط،';

    protected static ?string $modelLabel = 'ط­ظٹ';

    protected static ?string $pluralModelLabel = 'ط§ظ„ط£ط­ظٹط§ط،';

    protected static string|UnitEnum|null $navigationGroup = 'ط§ظ„ظ…ظˆط§ظ‚ط¹';

    public static function form(Schema $schema): Schema
    {
        return NeighborhoodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NeighborhoodsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNeighborhoods::route('/'),
            'create' => CreateNeighborhood::route('/create'),
            'edit' => EditNeighborhood::route('/{record}/edit'),
        ];
    }
}
