<?php

namespace App\Filament\Resources\Districts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DistrictsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('city.name_ar')
                    ->label('المدينة'),

                TextColumn::make('name_ar')
                    ->label('المديرية')
                    ->searchable(),

                TextColumn::make('name_en')
                    ->label('English'),

                TextColumn::make('code')
                    ->label('الرمز'),

                IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
