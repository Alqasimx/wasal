<?php

namespace App\Filament\Resources\Currencies\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الرمز الداخلي')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('iso_code')
                    ->label('ISO'),

                TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable(),

                TextColumn::make('symbol')
                    ->label('الرمز'),

                TextColumn::make('exchange_rate')
                    ->label('سعر الصرف'),

                IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}