<?php

namespace App\Filament\Resources\PropertyListings;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\PropertyListings\Pages\CreatePropertyListing;
use App\Filament\Resources\PropertyListings\Pages\EditPropertyListing;
use App\Filament\Resources\PropertyListings\Pages\ListPropertyListings;
use App\Filament\Resources\PropertyListings\Schemas\PropertyListingForm;
use App\Filament\Resources\PropertyListings\Tables\PropertyListingsTable;
use App\Models\PropertyListing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PropertyListingResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = PropertyListing::class;
    protected static string $viewPermission = 'property_listings.view';
    protected static string $managePermission = 'property_listings.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;
    protected static ?string $recordTitleAttribute = 'public_title';
    protected static ?string $navigationLabel = 'الإعلانات العقارية';
    protected static ?string $modelLabel = 'إعلان عقاري';
    protected static ?string $pluralModelLabel = 'الإعلانات العقارية';
    protected static string|UnitEnum|null $navigationGroup = 'العقارات';

    public static function form(Schema $schema): Schema { return PropertyListingForm::configure($schema); }
    public static function table(Table $table): Table { return PropertyListingsTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => ListPropertyListings::route('/'),
            'create' => CreatePropertyListing::route('/create'),
            'edit' => EditPropertyListing::route('/{record}/edit'),
        ];
    }
}
