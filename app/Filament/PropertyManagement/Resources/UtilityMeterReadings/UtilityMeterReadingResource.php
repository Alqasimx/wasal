<?php

namespace App\Filament\PropertyManagement\Resources\UtilityMeterReadings;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\UtilityMeterReadings\Pages\CreateUtilityMeterReading;
use App\Filament\PropertyManagement\Resources\UtilityMeterReadings\Pages\ListUtilityMeterReadings;
use App\Models\UtilityMeterReading;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
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

class UtilityMeterReadingResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = UtilityMeterReading::class;
    protected static string $viewPermission = 'utility_meter_readings.view';
    protected static string $managePermission = 'utility_meter_readings.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?string $navigationLabel = 'قراءات العدادات';
    protected static ?string $modelLabel = 'قراءة عداد';
    protected static ?string $pluralModelLabel = 'قراءات العدادات';
    protected static string|UnitEnum|null $navigationGroup = 'العمليات المتقدمة';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('utility_meter_id')
                ->label('العداد')
                ->relationship('meter', 'meter_number')
                ->getOptionLabelFromRecordUsing(fn ($record): string =>
                    $record->meter_number.' — '.($record->property?->internal_code ?? 'عقار'))
                ->searchable()
                ->preload()
                ->required(),

            DateTimePicker::make('reading_at')
                ->label('تاريخ القراءة')
                ->seconds(false)
                ->default(now())
                ->required(),

            TextInput::make('reading_value')
                ->label('القراءة الحالية')
                ->numeric()
                ->minValue(0)
                ->required(),

            FileUpload::make('attachment_path')
                ->label('صورة العداد')
                ->disk('public')
                ->directory('property-management/meter-readings')
                ->openable()
                ->downloadable(),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('reading_at', 'desc')
            ->columns([
                TextColumn::make('meter.meter_number')->label('العداد')->searchable(),
                TextColumn::make('meter.property.internal_code')->label('العقار'),
                TextColumn::make('meter.unit.code')->label('الوحدة')->placeholder('كامل العقار'),
                TextColumn::make('reading_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('previous_value')->label('السابقة')->numeric()->placeholder('—'),
                TextColumn::make('reading_value')->label('الحالية')->numeric(),
                TextColumn::make('consumption')->label('الاستهلاك')->numeric()->placeholder('—'),
                TextColumn::make('meter.unit_of_measure')->label('الوحدة'),
                TextColumn::make('recorder.name')->label('سجلها')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('utility_meter_id')
                    ->label('العداد')
                    ->relationship('meter', 'meter_number')
                    ->searchable()
                    ->preload(),
            ])
            ->striped();
    }

    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => ListUtilityMeterReadings::route('/'),
            'create' => CreateUtilityMeterReading::route('/create'),
        ];
    }
}
