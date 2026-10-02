<?php

namespace App\Filament\Resources\PropertyListings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            TextColumn::make('listing_number')->label('رقم الإعلان')->searchable()->sortable(),
            TextColumn::make('public_title')->label('العنوان')->searchable(),
            TextColumn::make('property.internal_code')->label('العقار'),
            TextColumn::make('purpose')->label('الغرض')->formatStateUsing(fn (string $state): string => $state === 'rent' ? 'إيجار' : 'بيع')->badge(),
            TextColumn::make('status')->label('الحالة')->badge(),
            IconColumn::make('share_enabled')->label('مشاركة')->boolean(),
            TextColumn::make('share_count')->label('المشاركات')->sortable(),
        ])->recordActions([EditAction::make()]);
    }
}
