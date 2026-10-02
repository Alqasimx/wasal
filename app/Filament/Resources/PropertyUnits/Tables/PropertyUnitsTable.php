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
        return $table->defaultSort('created_at','desc')->columns([
            ImageColumn::make('property_image')
                ->label('صورة العقار')
                ->state(fn ($record): ?string => $record->property?->gallery[0] ?? null)
                ->disk('public')
                ->height(104)
                ->width(148),
            TextColumn::make('property.internal_code')->label('العقار')->searchable(),
            TextColumn::make('code')->label('الرمز')->searchable(),
            TextColumn::make('name')->label('الوحدة')->searchable(),
            TextColumn::make('floor_number')->label('الطابق'),
            TextColumn::make('bedrooms')->label('الغرف'),
            TextColumn::make('bathrooms')->label('الحمامات'),
            TextColumn::make('status')->label('الحالة')->badge(),
        ])->striped()->defaultPaginationPageOption(10)->recordActions([EditAction::make()]);
    }
}
