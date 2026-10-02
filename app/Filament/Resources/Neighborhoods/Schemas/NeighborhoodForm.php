<?php

namespace App\Filament\Resources\Neighborhoods\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NeighborhoodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('district_id')
                    ->label('المديرية')
                    ->relationship('district', 'name_ar')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('code')
                    ->label('الرمز'),

                TextInput::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->required(),

                TextInput::make('name_en')
                    ->label('الاسم بالإنجليزية'),

                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }
}
