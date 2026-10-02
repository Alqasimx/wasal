<?php
namespace App\Filament\Resources\PropertyOwners\Schemas;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
class PropertyOwnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->label('العقار')->relationship('property', 'internal_code')->searchable()->preload()->required(),
            Select::make('user_id')->label('المستخدم المالك')->relationship('user', 'name')->searchable()->preload(),
            TextInput::make('external_owner_name')->label('اسم المالك الخارجي'),
            TextInput::make('ownership_percentage')->label('نسبة الملكية')->numeric()->minValue(0)->maxValue(100),
            Toggle::make('is_primary')->label('المالك الأساسي')->default(false),
            DatePicker::make('valid_from')->label('سارية من'),
            DatePicker::make('valid_to')->label('سارية إلى'),
        ]);
    }
}
