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
                    ->searchable(),

                TextColumn::make('action')
                    ->label('العملية')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entity_type')
                    ->label('نوع السجل')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('entity_id')
                    ->label('رقم السجل')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض'),
            ]);
    }
}