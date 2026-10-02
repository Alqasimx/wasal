<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('actor.name')
                    ->label('المستخدم')
                    ->placeholder('النظام')
                    ->searchable()
                    ->limit(22),

                TextColumn::make('action')
                    ->label('العملية')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('entity_type')
                    ->label('نوع السجل')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->searchable(),

                TextColumn::make('entity_id')
                    ->label('رقم السجل')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                ViewAction::make()
                    ->label('التفاصيل'),
            ]);
    }
}
