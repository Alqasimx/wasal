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
        return $table->columns([
            ImageColumn::make('gallery_preview')
                ->label('الصورة')
                ->state(fn (Property $record): ?string => $record->gallery[0] ?? null)
                ->disk('public')
                ->height(56)
                ->width(72)
                ->square(),
            TextColumn::make('internal_code')->label('الرمز')->searchable()->sortable(),
            TextColumn::make('title_ar')->label('العقار')->searchable()->sortable(),
            TextColumn::make('propertyType.name_ar')->label('النوع'),
            TextColumn::make('city.name_ar')->label('المدينة'),
            TextColumn::make('district.name_ar')->label('المديرية'),
            TextColumn::make('neighborhood.name_ar')->label('الحي'),
            TextColumn::make('street_name')->label('الشارع')->toggleable(),
            TextColumn::make('area')->label('المساحة')->suffix(' م²')->sortable()->toggleable(),
            TextColumn::make('units_count')->label('الوحدات')->sortable()->toggleable(),
            TextColumn::make('status')->label('الحالة')->badge(),
        ])->recordActions([EditAction::make()]);
    }
}
