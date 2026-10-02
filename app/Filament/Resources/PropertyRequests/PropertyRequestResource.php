<?php
namespace App\Filament\Resources\PropertyRequests;
use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\Resources\PropertyRequests\Pages\CreatePropertyRequest;
use App\Filament\Resources\PropertyRequests\Pages\EditPropertyRequest;
use App\Filament\Resources\PropertyRequests\Pages\ListPropertyRequests;
use App\Filament\Resources\PropertyRequests\Schemas\PropertyRequestForm;
use App\Filament\Resources\PropertyRequests\Tables\PropertyRequestsTable;
use App\Models\PropertyRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
class PropertyRequestResource extends Resource
{
    use HasResourcePermissions;
    protected static ?string $model = PropertyRequest::class;
    protected static string $viewPermission = 'property_requests.view';
    protected static string $managePermission = 'property_requests.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'طلبات العقار';
    protected static ?string $modelLabel = 'طلب عقار';
    protected static ?string $pluralModelLabel = 'طلبات العقار';
    protected static string|UnitEnum|null $navigationGroup = 'العقارات';
    public static function form(Schema $schema): Schema { return PropertyRequestForm::configure($schema); }
    public static function table(Table $table): Table { return PropertyRequestsTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return ['index'=>ListPropertyRequests::route('/'),'create'=>CreatePropertyRequest::route('/create'),'edit'=>EditPropertyRequest::route('/{record}/edit')]; }
}
