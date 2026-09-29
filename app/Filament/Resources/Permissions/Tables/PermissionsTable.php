<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الصلاحية')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles_count')
                    ->label('عدد الأدوار')
                    ->counts('roles')
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('Guard'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}