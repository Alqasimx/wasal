<?php

namespace App\Filament\Resources\Neighborhoods\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NeighborhoodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('district.name_ar')
                    ->label('المديرية'),

                TextColumn::make('name_ar')
                    ->label('الحي')
                    ->searchable(),

                TextColumn::make('name_en')
                    ->label('English'),

                TextColumn::make('code')
                    ->label('الرمز'),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}