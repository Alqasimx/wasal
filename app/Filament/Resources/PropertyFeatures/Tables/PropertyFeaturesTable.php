<?php

namespace App\Filament\Resources\PropertyFeatures\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyFeaturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_en')
                    ->label('English')
                    ->searchable(),

                TextColumn::make('data_type')
                    ->label('نوع القيمة')
                    ->badge(),

                IconColumn::make('is_filterable')
                    ->label('فلتر')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
