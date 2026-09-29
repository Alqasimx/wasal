<?php

namespace App\Filament\Resources\Banks\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BanksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('البنك')
                    ->searchable(),

                TextColumn::make('account_name')
                    ->label('اسم الحساب')
                    ->searchable(),

                TextColumn::make('account_number')
                    ->label('رقم الحساب')
                    ->searchable(),

                TextColumn::make('currency.name_ar')
                    ->label('العملة'),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}