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
            TextColumn::make('share_url')
                ->label('رابط المشاركة')
                ->state(fn ($record): ?string => $record->share_enabled && $record->share_token
                    ? url('/api/v1/shared-offers/'.$record->share_token)
                    : null)
                ->copyable()
                ->limit(35),
        ])->recordActions([EditAction::make()]);
    }
}
