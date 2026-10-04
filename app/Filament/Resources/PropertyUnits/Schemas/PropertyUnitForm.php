<?php

namespace App\Filament\Resources\PropertyUnits\Schemas;

use App\Models\PropertyUnit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PropertyUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')
                ->label('العقار (حسب الرمز الداخلي)')
                ->relationship('property', 'internal_code')
                ->getOptionLabelFromRecordUsing(
                    fn ($record): string => $record->internal_code.' — '.$record->title_ar
                )
                ->searchable(['internal_code', 'title_ar'])
                ->preload()
                ->required(),

            TextInput::make('code')
                ->label('رمز الوحدة')
                ->required(),

            TextInput::make('name')
                ->label('اسم الوحدة')
                ->required(),

            TextInput::make('floor_number')
                ->label('الطابق')
                ->numeric(),

            TextInput::make('unit_type')
                ->label('نوع الوحدة')
                ->placeholder('شقة / محل / مكتب'),

            TextInput::make('area')
                ->label('المساحة')
                ->numeric(),

            TextInput::make('bedrooms')
                ->label('غرف النوم')
                ->numeric(),

            TextInput::make('bathrooms')
                ->label('الحمامات')
                ->numeric(),

            TextInput::make('halls')
                ->label('الصالات')
                ->numeric(),

            Select::make('status')
                ->label('الحالة التشغيلية')
                ->options([
                    PropertyUnit::STATUS_AVAILABLE => 'متاحة للتأجير',
                    PropertyUnit::STATUS_OCCUPIED => 'مشغولة — يعتمد الإشغال الفعلي على العقد النشط',
                    PropertyUnit::STATUS_MAINTENANCE => 'تحت الصيانة',
                    PropertyUnit::STATUS_UNAVAILABLE => 'غير متاحة / مغلقة',
                ])
                ->helperText('الإشغال الفعلي يُحسب تلقائيًا من عقد الإيجار النشط، بينما هذه الحالة تستخدم للتشغيل والصيانة.')
                ->required()
                ->default(PropertyUnit::STATUS_AVAILABLE),
        ]);
    }
}
