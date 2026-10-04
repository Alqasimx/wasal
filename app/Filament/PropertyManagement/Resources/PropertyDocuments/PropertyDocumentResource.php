<?php

namespace App\Filament\PropertyManagement\Resources\PropertyDocuments;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\PropertyDocuments\Pages\CreatePropertyDocument;
use App\Filament\PropertyManagement\Resources\PropertyDocuments\Pages\EditPropertyDocument;
use App\Filament\PropertyManagement\Resources\PropertyDocuments\Pages\ListPropertyDocuments;
use App\Models\PropertyDocument;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PropertyDocumentResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = PropertyDocument::class;
    protected static string $viewPermission = 'property_documents.view';
    protected static string $managePermission = 'property_documents.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?string $navigationLabel = 'المستندات والمرفقات';
    protected static ?string $modelLabel = 'مستند';
    protected static ?string $pluralModelLabel = 'المستندات والمرفقات';
    protected static string|UnitEnum|null $navigationGroup = 'العمليات المتقدمة';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')
                ->label('العقار')
                ->relationship('property', 'internal_code')
                ->getOptionLabelFromRecordUsing(fn ($record): string =>
                    $record->internal_code.' — '.$record->title_ar)
                ->searchable(['internal_code', 'title_ar'])
                ->preload()
                ->required(),

            Select::make('property_unit_id')
                ->label('الوحدة')
                ->relationship('unit', 'code')
                ->searchable()
                ->preload(),

            Select::make('tenancy_id')
                ->label('عقد الإيجار')
                ->relationship('tenancy', 'contract_number')
                ->searchable()
                ->preload(),

            Select::make('property_management_agreement_id')
                ->label('اتفاق الإدارة')
                ->relationship('agreement', 'agreement_number')
                ->searchable()
                ->preload(),

            TextInput::make('title')
                ->label('اسم المستند')
                ->required()
                ->maxLength(255),

            Select::make('document_type')
                ->label('نوع المستند')
                ->options([
                    'ownership' => 'ملكية',
                    'management_agreement' => 'اتفاق إدارة',
                    'tenancy_contract' => 'عقد إيجار',
                    'handover' => 'استلام / تسليم',
                    'invoice' => 'فاتورة',
                    'permit' => 'تصريح / ترخيص',
                    'identity' => 'هوية',
                    'other' => 'أخرى',
                ])
                ->required(),

            FileUpload::make('file_path')
                ->label('الملف')
                ->disk('public')
                ->directory('property-management/documents')
                ->openable()
                ->downloadable(),

            DatePicker::make('issued_at')
                ->label('تاريخ الإصدار'),

            DatePicker::make('expires_at')
                ->label('تاريخ الانتهاء')
                ->afterOrEqual('issued_at'),

            Select::make('status')
                ->label('الحالة')
                ->options([
                    PropertyDocument::STATUS_ACTIVE => 'نشط',
                    PropertyDocument::STATUS_EXPIRED => 'منتهي',
                    PropertyDocument::STATUS_ARCHIVED => 'مؤرشف',
                ])
                ->default(PropertyDocument::STATUS_ACTIVE)
                ->required(),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('المستند')->searchable(),
                TextColumn::make('document_type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ownership' => 'ملكية',
                        'management_agreement' => 'اتفاق إدارة',
                        'tenancy_contract' => 'عقد إيجار',
                        'handover' => 'استلام / تسليم',
                        'invoice' => 'فاتورة',
                        'permit' => 'تصريح / ترخيص',
                        'identity' => 'هوية',
                        default => 'أخرى',
                    })
                    ->badge(),
                TextColumn::make('property.internal_code')->label('العقار')->searchable(),
                TextColumn::make('unit.code')->label('الوحدة')->placeholder('—'),
                TextColumn::make('issued_at')->label('الإصدار')->date('Y-m-d')->placeholder('—'),
                TextColumn::make('expires_at')->label('الانتهاء')->date('Y-m-d')->placeholder('بدون انتهاء')->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyDocument::STATUS_EXPIRED => 'منتهي',
                        PropertyDocument::STATUS_ARCHIVED => 'مؤرشف',
                        default => 'نشط',
                    })
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('النوع')
                    ->options([
                        'ownership' => 'ملكية',
                        'management_agreement' => 'اتفاق إدارة',
                        'tenancy_contract' => 'عقد إيجار',
                        'handover' => 'استلام / تسليم',
                        'invoice' => 'فاتورة',
                        'permit' => 'تصريح / ترخيص',
                        'identity' => 'هوية',
                        'other' => 'أخرى',
                    ]),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        PropertyDocument::STATUS_ACTIVE => 'نشط',
                        PropertyDocument::STATUS_EXPIRED => 'منتهي',
                        PropertyDocument::STATUS_ARCHIVED => 'مؤرشف',
                    ]),
            ])
            ->striped()
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ]);
    }

    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyDocuments::route('/'),
            'create' => CreatePropertyDocument::route('/create'),
            'edit' => EditPropertyDocument::route('/{record}/edit'),
        ];
    }
}
