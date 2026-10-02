<?php

namespace App\Filament\Resources\Properties\Schemas;

use App\Models\District;
use App\Models\Neighborhood;
use App\Models\Street;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_type_id')->label('نوع العقار')->relationship('propertyType', 'name_ar')->searchable()->preload()->required(),
            TextInput::make('internal_code')->label('الرمز الداخلي')->required()->unique(ignoreRecord: true),
            TextInput::make('title_ar')->label('العنوان بالعربية')->required(),
            TextInput::make('title_en')->label('العنوان بالإنجليزية'),
            Textarea::make('description_ar')->label('الوصف بالعربية')->rows(3),
            Select::make('status')->label('الحالة')->options(['draft' => 'مسودة', 'active' => 'نشط', 'archived' => 'مؤرشف'])->required()->default('draft'),

            Select::make('city_id')
                ->label('المدينة')
                ->relationship('city', 'name_ar')
                ->searchable()->preload()->live()->required()
                ->afterStateUpdated(function (Set $set): void {
                    $set('district_id', null);
                    $set('neighborhood_id', null);
                    $set('street_name', null);
                }),
            Select::make('district_id')
                ->label('المديرية')
                ->options(fn (Get $get): array => $get('city_id')
                    ? District::query()->where('city_id', $get('city_id'))->where('is_active', true)->orderBy('name_ar')->pluck('name_ar', 'id')->all()
                    : [])
                ->searchable()->preload()->live()
                ->afterStateUpdated(function (Set $set): void {
                    $set('neighborhood_id', null);
                    $set('street_name', null);
                }),
            Select::make('neighborhood_id')
                ->label('الحي')
                ->options(fn (Get $get): array => $get('district_id')
                    ? Neighborhood::query()->where('district_id', $get('district_id'))->where('is_active', true)->orderBy('name_ar')->pluck('name_ar', 'id')->all()
                    : [])
                ->searchable()->preload(),
            Select::make('street_name')
                ->label('الشارع')
                ->options(fn (Get $get): array => $get('district_id')
                    ? Street::query()->where('district_id', $get('district_id'))->where('is_active', true)->orderBy('sort_order')->orderBy('name_ar')->pluck('name_ar', 'name_ar')->all()
                    : [])
                ->searchable()->preload(),

            TextInput::make('public_location_text')->label('وصف الموقع العام'),
            Textarea::make('exact_address')->label('العنوان الدقيق للإدارة')->rows(2),
            TextInput::make('public_latitude')->label('خط العرض العام')->numeric()->minValue(-90)->maxValue(90),
            TextInput::make('public_longitude')->label('خط الطول العام')->numeric()->minValue(-180)->maxValue(180),
            TextInput::make('exact_latitude')->label('خط العرض الدقيق للإدارة')->numeric()->minValue(-90)->maxValue(90),
            TextInput::make('exact_longitude')->label('خط الطول الدقيق للإدارة')->numeric()->minValue(-180)->maxValue(180),
            TextInput::make('area')->label('المساحة')->numeric(),
            TextInput::make('floors_count')->label('عدد الطوابق')->numeric(),
            TextInput::make('units_count')->label('عدد الوحدات')->numeric(),
            TextInput::make('year_built')->label('سنة البناء')->numeric(),
            FileUpload::make('gallery')
                ->label('صور العقار')
                ->helperText('يمكن رفع عدة صور وترتيبها. لا ترفعي مستندات هوية أو بيانات سرية هنا.')
                ->disk('public')
                ->directory('properties')
                ->image()
                ->multiple()
                ->reorderable()
                ->appendFiles()
                ->openable()
                ->downloadable()
                ->maxFiles(20)
                ->maxSize(10240),
        ]);
    }
}
