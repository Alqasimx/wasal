<?php

namespace App\Filament\Resources\PropertyOwners\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyOwnersTable
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

                TextColumn::make('user.name')
                    ->label('المالك')
                    ->searchable()
                    ->placeholder('—')
                    ->limit(24),

                TextColumn::make('external_owner_name')
                    ->label('مالك خارجي')
                    ->placeholder('—')
                    ->limit(24),

                TextColumn::make('ownership_percentage')
                    ->label('نسبة الملكية')
                    ->suffix('%')
                    ->placeholder('—'),

                IconColumn::make('is_primary')
                    ->label('أساسي')
                    ->boolean(),

                TextColumn::make('valid_from')
                    ->label('سارية من')
                    ->date('Y-m-d')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('valid_to')
                    ->label('سارية إلى')
                    ->date('Y-m-d')
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
