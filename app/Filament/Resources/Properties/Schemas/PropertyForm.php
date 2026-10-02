<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

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
            Select::make('city_id')->label('المدينة')->relationship('city', 'name_ar')->searchable()->preload()->required(),
            Select::make('district_id')->label('المديرية')->relationship('district', 'name_ar')->searchable()->preload(),
            Select::make('neighborhood_id')->label('الحي')->relationship('neighborhood', 'name_ar')->searchable()->preload(),
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
        ]);
    }
}
