<?php

namespace App\Filament\Resources\Properties\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('internal_code')->label('الرمز')->searchable()->sortable(),
            TextColumn::make('title_ar')->label('العقار')->searchable()->sortable(),
            TextColumn::make('propertyType.name_ar')->label('النوع'),
            TextColumn::make('city.name_ar')->label('المدينة'),
            TextColumn::make('status')->label('الحالة')->badge(),
        ])->recordActions([EditAction::make()]);
    }
}
