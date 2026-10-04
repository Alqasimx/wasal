<?php

namespace App\Filament\PropertyManagement\Resources\PropertyServices;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\PropertyServices\Pages\CreatePropertyService;
use App\Filament\PropertyManagement\Resources\PropertyServices\Pages\EditPropertyService;
use App\Filament\PropertyManagement\Resources\PropertyServices\Pages\ListPropertyServices;
use App\Models\PropertyService;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class PropertyServiceResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = PropertyService::class;
    protected static string $viewPermission = 'property_services.view';
    protected static string $managePermission = 'property_services.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;
    protected static ?string $navigationLabel = 'كتالوج الخدمات';
    protected static ?string $modelLabel = 'خدمة';
    protected static ?string $pluralModelLabel = 'كتالوج الخدمات';
    protected static string|UnitEnum|null $navigationGroup = 'الخدمات والصيانة';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('رمز الخدمة')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),

            TextInput::make('name_ar')
                ->label('اسم الخدمة')
                ->required()
                ->maxLength(255),

            TextInput::make('name_en')
                ->label('الاسم بالإنجليزية')
                ->maxLength(255),

            Select::make('category')
                ->label('التصنيف')
                ->options([
                    PropertyService::CATEGORY_MAINTENANCE => 'صيانة',
                    PropertyService::CATEGORY_CLEANING => 'تنظيف',
                    PropertyService::CATEGORY_INSPECTION => 'فحص',
                    PropertyService::CATEGORY_UTILITY => 'مرافق وخدمات',
                    PropertyService::CATEGORY_ADMIN => 'إدارية',
                    PropertyService::CATEGORY_FINANCIAL => 'مالية',
                ])
                ->required(),

            Textarea::make('description')
                ->label('الوصف')
                ->rows(3),

            Select::make('default_frequency')
                ->label('التكرار الافتراضي')
                ->options([
                    PropertyService::FREQUENCY_ONCE => 'مرة واحدة',
                    PropertyService::FREQUENCY_DAILY => 'يومي',
                    PropertyService::FREQUENCY_WEEKLY => 'أسبوعي',
                    PropertyService::FREQUENCY_MONTHLY => 'شهري',
                    PropertyService::FREQUENCY_QUARTERLY => 'كل 3 أشهر',
                    PropertyService::FREQUENCY_SEMIANNUAL => 'كل 6 أشهر',
                    PropertyService::FREQUENCY_ANNUAL => 'سنوي',
                    PropertyService::FREQUENCY_CUSTOM_DAYS => 'أيام مخصصة',
                ])
                ->default(PropertyService::FREQUENCY_ONCE)
                ->required(),

            TextInput::make('default_interval')
                ->label('معامل التكرار')
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->required(),

            TextInput::make('default_cost')
                ->label('التكلفة التقديرية الافتراضية')
                ->numeric()
                ->minValue(0),

            Select::make('currency_id')
                ->label('العملة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload(),

            TextInput::make('notify_before_minutes')
                ->label('التنبيه قبل الموعد بالدقائق')
                ->numeric()
                ->minValue(0)
                ->default(1440)
                ->required(),

            Toggle::make('creates_task')
                ->label('إنشاء مهمة تلقائيًا')
                ->default(true),

            Toggle::make('is_active')
                ->label('الخدمة نشطة')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name_ar')
            ->columns([
                TextColumn::make('code')
                    ->label('الرمز')
                    ->searchable(),

                TextColumn::make('name_ar')
                    ->label('الخدمة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('التصنيف')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyService::CATEGORY_CLEANING => 'تنظيف',
                        PropertyService::CATEGORY_INSPECTION => 'فحص',
                        PropertyService::CATEGORY_UTILITY => 'مرافق وخدمات',
                        PropertyService::CATEGORY_ADMIN => 'إدارية',
                        PropertyService::CATEGORY_FINANCIAL => 'مالية',
                        default => 'صيانة',
                    })
                    ->badge(),

                TextColumn::make('default_frequency')
                    ->label('التكرار')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyService::FREQUENCY_DAILY => 'يومي',
                        PropertyService::FREQUENCY_WEEKLY => 'أسبوعي',
                        PropertyService::FREQUENCY_MONTHLY => 'شهري',
                        PropertyService::FREQUENCY_QUARTERLY => 'كل 3 أشهر',
                        PropertyService::FREQUENCY_SEMIANNUAL => 'كل 6 أشهر',
                        PropertyService::FREQUENCY_ANNUAL => 'سنوي',
                        PropertyService::FREQUENCY_CUSTOM_DAYS => 'أيام مخصصة',
                        default => 'مرة واحدة',
                    })
                    ->badge(),

                IconColumn::make('creates_task')
                    ->label('ينشئ مهمة')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options([
                        PropertyService::CATEGORY_MAINTENANCE => 'صيانة',
                        PropertyService::CATEGORY_CLEANING => 'تنظيف',
                        PropertyService::CATEGORY_INSPECTION => 'فحص',
                        PropertyService::CATEGORY_UTILITY => 'مرافق وخدمات',
                        PropertyService::CATEGORY_ADMIN => 'إدارية',
                        PropertyService::CATEGORY_FINANCIAL => 'مالية',
                    ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyServices::route('/'),
            'create' => CreatePropertyService::route('/create'),
            'edit' => EditPropertyService::route('/{record}/edit'),
        ];
    }
}
