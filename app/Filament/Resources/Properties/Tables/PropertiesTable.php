<?php

namespace App\Filament\Resources\Properties\Tables;

use App\Models\Property;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('gallery_preview')
                    ->label('الصورة')
                    ->state(fn (Property $record): ?string => $record->gallery[0] ?? null)
                    ->disk('public')
                    ->height(96)
                    ->width(136),

                TextColumn::make('internal_code')
                    ->label('الرمز')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('title_ar')
                    ->label('العقار')
                    ->searchable()
                    ->sortable()
                    ->limit(24)
                    ->wrap(),

                TextColumn::make('propertyType.name_ar')
                    ->label('النوع')
                    ->placeholder('—'),

                TextColumn::make('city.name_ar')
                    ->label('المدينة')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('area')
                    ->label('المساحة')
                    ->suffix(' م²')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('district.name_ar')
                    ->label('المديرية')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('neighborhood.name_ar')
                    ->label('الحي')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('street_name')
                    ->label('الشارع')
                    ->limit(28)
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('units_count')
                    ->label('الوحدات')
                    ->sortable()
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
