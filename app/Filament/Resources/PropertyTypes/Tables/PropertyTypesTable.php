<?php

namespace App\Filament\Resources\PropertyTypes\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyTypesTable
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

                TextColumn::make('slug')
                    ->label('المعرف')
                    ->searchable(),

                TextColumn::make('features_count')
                    ->counts('features')
                    ->label('عدد الخصائص'),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
