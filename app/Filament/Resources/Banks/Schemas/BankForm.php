<?php

namespace App\Filament\Resources\Banks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم البنك')
                    ->required(),

                TextInput::make('account_name')
                    ->label('اسم الحساب')
                    ->required(),

                TextInput::make('account_number')
                    ->label('رقم الحساب')
                    ->required(),

                TextInput::make('iban')
                    ->label('IBAN'),

                Select::make('currency_id')
                    ->label('العملة')
                    ->relationship('currency', 'name_ar')
                    ->searchable()
                    ->preload()
                    ->required(),

                Textarea::make('instructions')
                    ->label('تعليمات الإيداع')
                    ->rows(4),

                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),

                TextInput::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
