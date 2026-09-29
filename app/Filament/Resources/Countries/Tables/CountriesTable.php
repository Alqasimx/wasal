<?php

namespace App\Filament\Resources\Countries\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الرمز')
                    ->searchable(),

                TextColumn::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->searchable(),

                TextColumn::make('name_en')
                    ->label('الاسم بالإنجليزية')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}