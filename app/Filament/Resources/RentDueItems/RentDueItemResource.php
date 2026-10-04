<?php

namespace App\Filament\Resources\RentDueItems;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\RentDueItems\Pages\CreateRentDueItem;
use App\Filament\Resources\RentDueItems\Pages\EditRentDueItem;
use App\Filament\Resources\RentDueItems\Pages\ListRentDueItems;
use App\Filament\Resources\RentDueItems\Schemas\RentDueItemForm;
use App\Filament\Resources\RentDueItems\Tables\RentDueItemsTable;
use App\Models\RentDueItem;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RentDueItemResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = RentDueItem::class;
    protected static string $viewPermission = 'rent_due_items.view';
    protected static string $managePermission = 'rent_due_items.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'استحقاقات الإيجار';
    protected static ?string $modelLabel = 'استحقاق إيجار';
    protected static ?string $pluralModelLabel = 'استحقاقات الإيجار';
    protected static string|UnitEnum|null $navigationGroup = 'إدارة الأملاك';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return Filament::getCurrentPanel()?->getId() === 'property-management'
            ? 'الإيجارات والتحصيل'
            : 'إدارة الأملاك';
    }

    public static function form(Schema $schema): Schema
    {
        return RentDueItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentDueItemsTable::configure($table);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentDueItems::route('/'),
            'create' => CreateRentDueItem::route('/create'),
            'edit' => EditRentDueItem::route('/{record}/edit'),
        ];
    }
}
