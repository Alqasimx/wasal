<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Models\Task;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('due_at')
            ->columns([
                TextColumn::make('title')
                    ->label('المهمة')
                    ->searchable()
                    ->sortable()
                    ->limit(32)
                    ->wrap(),

                TextColumn::make('assignee.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->placeholder('غير معين')
                    ->limit(22),

                TextColumn::make('due_at')
                    ->label('الاستحقاق')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Task::STATUS_IN_PROGRESS => 'قيد التنفيذ',
                        Task::STATUS_COMPLETED => 'مكتملة',
                        Task::STATUS_CANCELLED => 'ملغاة',
                        default => 'جديدة',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Task::STATUS_COMPLETED => 'success',
                        Task::STATUS_CANCELLED => 'gray',
                        Task::STATUS_IN_PROGRESS => 'info',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Task::PRIORITY_URGENT => 'عاجلة',
                        Task::PRIORITY_HIGH => 'عالية',
                        Task::PRIORITY_LOW => 'منخفضة',
                        default => 'عادية',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Task::PRIORITY_URGENT => 'danger',
                        Task::PRIORITY_HIGH => 'warning',
                        Task::PRIORITY_LOW => 'gray',
                        default => 'info',
                    })
                    ->sortable(),

                TextColumn::make('recurrence')
                    ->label('التكرار')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Task::RECURRENCE_DAILY => 'يومي',
                        Task::RECURRENCE_WEEKLY => 'أسبوعي',
                        default => 'مرة واحدة',
                    })
                    ->badge()
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
