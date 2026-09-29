<?php

namespace App\Filament\Resources\Governorates\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GovernoratesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('country.name_ar')
                    ->label('الدولة'),

                TextColumn::make('name_ar')
                    ->label('المحافظة')
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