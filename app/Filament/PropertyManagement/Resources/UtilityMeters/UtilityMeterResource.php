<?php

namespace App\Filament\PropertyManagement\Resources\UtilityMeters;

use App\Filament\Concerns\HasResourcePermissions;
use App\Filament\PropertyManagement\Resources\UtilityMeters\Pages\CreateUtilityMeter;
use App\Filament\PropertyManagement\Resources\UtilityMeters\Pages\EditUtilityMeter;
use App\Filament\PropertyManagement\Resources\UtilityMeters\Pages\ListUtilityMeters;
use App\Models\UtilityMeter;
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
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UtilityMeterResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = UtilityMeter::class;
    protected static string $viewPermission = 'utility_meters.view';
    protected static string $managePermission = 'utility_meters.manage';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;
    protected static ?string $navigationLabel = 'العدادات والمرافق';
    protected static ?string $modelLabel = 'عداد';
    protected static ?string $pluralModelLabel = 'العدادات والمرافق';
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

            Select::make('meter_type')
                ->label('نوع العداد')
                ->options([
                    UtilityMeter::TYPE_ELECTRICITY => 'كهرباء',
                    UtilityMeter::TYPE_WATER => 'مياه',
                    UtilityMeter::TYPE_GAS => 'غاز',
                    UtilityMeter::TYPE_OTHER => 'أخرى',
                ])
                ->required(),

            TextInput::make('meter_number')
                ->label('رقم العداد')
                ->required()
                ->maxLength(255),

            TextInput::make('unit_of_measure')
                ->label('وحدة القياس')
                ->placeholder('kWh / m³')
                ->required(),

            Toggle::make('is_active')
                ->label('عداد نشط')
                ->default(true),

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
                TextColumn::make('meter_number')->label('رقم العداد')->searchable()->copyable(),
                TextColumn::make('meter_type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        UtilityMeter::TYPE_ELECTRICITY => 'كهرباء',
                        UtilityMeter::TYPE_WATER => 'مياه',
                        UtilityMeter::TYPE_GAS => 'غاز',
                        default => 'أخرى',
                    })
                    ->badge(),
                TextColumn::make('property.internal_code')->label('العقار')->searchable(),
                TextColumn::make('unit.code')->label('الوحدة')->placeholder('كامل العقار'),
                TextColumn::make('unit_of_measure')->label('وحدة القياس'),
                TextColumn::make('last_reading')
                    ->label('آخر قراءة')
                    ->state(fn (UtilityMeter $record): string =>
                        ($record->readings()->latest('reading_at')->value('reading_value') ?? '—')
                    ),
                TextColumn::make('last_reading_at')
                    ->label('تاريخ آخر قراءة')
                    ->state(fn (UtilityMeter $record) =>
                        $record->readings()->latest('reading_at')->value('reading_at'))
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('—'),
                IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->filters([
                SelectFilter::make('meter_type')
                    ->label('النوع')
                    ->options([
                        UtilityMeter::TYPE_ELECTRICITY => 'كهرباء',
                        UtilityMeter::TYPE_WATER => 'مياه',
                        UtilityMeter::TYPE_GAS => 'غاز',
                        UtilityMeter::TYPE_OTHER => 'أخرى',
                    ]),
                SelectFilter::make('is_active')
                    ->label('الحالة')
                    ->options([1 => 'نشط', 0 => 'موقوف']),
            ])
            ->striped()
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ]);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUtilityMeters::route('/'),
            'create' => CreateUtilityMeter::route('/create'),
            'edit' => EditUtilityMeter::route('/{record}/edit'),
        ];
    }
}
