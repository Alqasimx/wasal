<?php

namespace App\Filament\PropertyManagement\Resources\PropertyServiceSchedules;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\Pages\CreatePropertyServiceSchedule;
use App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\Pages\EditPropertyServiceSchedule;
use App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\Pages\ListPropertyServiceSchedules;
use App\Models\PropertyService;
use App\Models\PropertyServiceSchedule;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
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

class PropertyServiceScheduleResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = PropertyServiceSchedule::class;
    protected static string $viewPermission = 'property_service_schedules.view';
    protected static string $managePermission = 'property_service_schedules.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?string $navigationLabel = 'جدولة الخدمات';
    protected static ?string $modelLabel = 'جدول خدمة';
    protected static ?string $pluralModelLabel = 'جدولة الخدمات';
    protected static string|UnitEnum|null $navigationGroup = 'الخدمات والصيانة';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_service_id')
                ->label('الخدمة')
                ->relationship('service', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('property_id')
                ->label('العقار')
                ->relationship('property', 'internal_code')
                ->getOptionLabelFromRecordUsing(fn ($record): string => $record->internal_code.' — '.$record->title_ar)
                ->searchable(['internal_code', 'title_ar'])
                ->preload()
                ->required(),

            Select::make('property_unit_id')
                ->label('الوحدة')
                ->relationship('unit', 'code')
                ->getOptionLabelFromRecordUsing(fn ($record): string => $record->code.' — '.$record->name)
                ->searchable(['code', 'name'])
                ->preload(),

            Select::make('property_vendor_id')
                ->label('المورد / الفني')
                ->relationship('vendor', 'name')
                ->searchable()
                ->preload(),

            Select::make('assigned_to_user_id')
                ->label('المسؤول الداخلي')
                ->relationship('assignee', 'name')
                ->searchable()
                ->preload(),

            Select::make('frequency')
                ->label('التكرار')
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
                ->required()
                ->default(PropertyService::FREQUENCY_MONTHLY),

            TextInput::make('interval_count')
                ->label('معامل التكرار')
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->required(),

            DatePicker::make('starts_at')
                ->label('بداية الجدول')
                ->required(),

            DateTimePicker::make('next_due_at')
                ->label('الموعد القادم')
                ->seconds(false)
                ->required(),

            TextInput::make('notify_before_minutes')
                ->label('التنبيه قبل الموعد بالدقائق')
                ->numeric()
                ->minValue(0)
                ->default(1440)
                ->required(),

            TextInput::make('estimated_cost')
                ->label('التكلفة التقديرية')
                ->numeric()
                ->minValue(0),

            Select::make('currency_id')
                ->label('العملة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload(),

            Toggle::make('is_active')
                ->label('الجدول نشط')
                ->default(true),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('next_due_at')
            ->columns([
                TextColumn::make('service.name_ar')
                    ->label('الخدمة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('property.internal_code')
                    ->label('العقار')
                    ->searchable(),

                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->placeholder('كامل العقار'),

                TextColumn::make('next_due_at')
                    ->label('الموعد القادم')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('frequency')
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

                TextColumn::make('assignee.name')
                    ->label('المسؤول')
                    ->placeholder('تلقائي'),

                TextColumn::make('vendor.name')
                    ->label('المورد')
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('الحالة')
                    ->options([1 => 'نشط', 0 => 'موقوف']),

                SelectFilter::make('property_service_id')
                    ->label('الخدمة')
                    ->relationship('service', 'name_ar')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('property_id')
                    ->label('العقار')
                    ->relationship('property', 'internal_code')
                    ->searchable()
                    ->preload(),
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
            'index' => ListPropertyServiceSchedules::route('/'),
            'create' => CreatePropertyServiceSchedule::route('/create'),
            'edit' => EditPropertyServiceSchedule::route('/{record}/edit'),
        ];
    }
}
