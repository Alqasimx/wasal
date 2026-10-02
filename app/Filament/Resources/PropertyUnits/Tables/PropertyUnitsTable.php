<?php

namespace App\Filament\Resources\PropertyUnits\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('property_image')
                    ->label('الصورة')
                    ->state(fn ($record): ?string => $record->property?->gallery[0] ?? null)
                    ->disk('public')
                    ->height(68)
                    ->width(96),

                TextColumn::make('property.internal_code')
                    ->label('العقار')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('code')
                    ->label('رمز الوحدة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('الوحدة')
                    ->searchable()
                    ->limit(24),

                TextColumn::make('floor_number')
                    ->label('الطابق')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('bedrooms')
                    ->label('الغرف')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bathrooms')
                    ->label('الحمامات')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('area')
                    ->label('المساحة')
                    ->suffix(' م²')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()
                    ->label('تعديل'),
            ]);
    }
}
