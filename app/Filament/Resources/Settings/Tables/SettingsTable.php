<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('المفتاح')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('value_json')
                    ->label('القيمة')
                    ->limit(40),

                TextColumn::make('group')
                    ->label('المجموعة')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_public')
                    ->label('عام')
                    ->boolean(),

                TextColumn::make('updatedBy.name')
                    ->label('آخر تعديل بواسطة')
                    ->placeholder('—'),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}