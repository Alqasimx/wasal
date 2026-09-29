<?php

namespace App\Filament\Resources\Banks;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\Banks\Pages\CreateBank;
use App\Filament\Resources\Banks\Pages\EditBank;
use App\Filament\Resources\Banks\Pages\ListBanks;
use App\Filament\Resources\Banks\Schemas\BankForm;
use App\Filament\Resources\Banks\Tables\BanksTable;
use App\Models\Bank;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BankResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Bank::class;

    protected static string $viewPermission = 'banks.view';

    protected static string $managePermission = 'banks.manage';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'الحسابات البنكية';

    protected static ?string $modelLabel = 'حساب بنكي';

    protected static ?string $pluralModelLabel = 'الحسابات البنكية';

    protected static string|UnitEnum|null $navigationGroup = 'المالية';

    public static function form(Schema $schema): Schema
    {
        return BankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BanksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBanks::route('/'),
            'create' => CreateBank::route('/create'),
            'edit' => EditBank::route('/{record}/edit'),
        ];
    }
}