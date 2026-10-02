<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('الرمز')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->required(),

                TextInput::make('name_en')
                    ->label('الاسم بالإنجليزية'),

                Toggle::make('is_active')
                    ->label('نشطة')
                    ->default(true),
            ]);
    }
}
