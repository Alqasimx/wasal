<?php

namespace App\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('الرمز الداخلي')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),

                TextInput::make('iso_code')
                    ->label('رمز ISO')
                    ->maxLength(10),

                TextInput::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->required()
                    ->maxLength(255),

                TextInput::make('name_en')
                    ->label('الاسم بالإنجليزية')
                    ->required()
                    ->maxLength(255),

                TextInput::make('symbol')
                    ->label('الرمز')
                    ->maxLength(20),

                TextInput::make('exchange_rate')
                    ->label('سعر الصرف')
                    ->numeric(),

                Toggle::make('is_active')
                    ->label('نشطة')
                    ->default(true),
            ]);
    }
}
