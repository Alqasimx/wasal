<?php
namespace App\Filament\Resources\PropertyUnits\Tables;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class PropertyUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('created_at','desc')->columns([
            TextColumn::make('property.internal_code')->label('العقار')->searchable(),
            TextColumn::make('code')->label('الرمز')->searchable(),
            TextColumn::make('name')->label('الوحدة')->searchable(),
            TextColumn::make('floor_number')->label('الطابق'),
            TextColumn::make('bedrooms')->label('الغرف'),
            TextColumn::make('bathrooms')->label('الحمامات'),
            TextColumn::make('status')->label('الحالة')->badge(),
        ])->recordActions([EditAction::make()]);
    }
}
