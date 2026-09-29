<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('مفتاح الإعداد')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('value_json')
                    ->label('القيمة')
                    ->required(),

                TextInput::make('group')
                    ->label('المجموعة')
                    ->required()
                    ->default('general')
                    ->maxLength(100),

                Toggle::make('is_public')
                    ->label('متاح للعامة')
                    ->default(false),
            ]);
    }
}