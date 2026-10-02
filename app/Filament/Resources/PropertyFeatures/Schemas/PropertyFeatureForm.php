<?php

namespace App\Filament\Resources\PropertyFeatures\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PropertyFeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->required()
                    ->maxLength(255),

                TextInput::make('name_en')
                    ->label('الاسم بالإنجليزية')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->label('المعرف')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->maxLength(255),

                Select::make('data_type')
                    ->label('نوع القيمة')
                    ->options([
                        'boolean' => 'نعم / لا',
                        'integer' => 'رقم صحيح',
                        'decimal' => 'رقم عشري',
                        'text' => 'نص',
                        'select' => 'اختيار واحد',
                        'multiselect' => 'اختيارات متعددة',
                    ])
                    ->required()
                    ->default('text'),

                TagsInput::make('options')
                    ->label('القيم المتاحة')
                    ->placeholder('أضف قيمة ثم اضغط Enter')
                    ->helperText('تستخدم مع اختيار واحد أو اختيارات متعددة.'),

                TextInput::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Toggle::make('is_filterable')
                    ->label('تظهر كفلتر بحث')
                    ->default(false),

                Toggle::make('is_active')
                    ->label('نشطة')
                    ->default(true),
            ]);
    }
}
