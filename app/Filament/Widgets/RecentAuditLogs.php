<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentAuditLogs extends TableWidget
{
    protected static ?string $heading = 'آخر العمليات';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AuditLog::query()
                    ->with('actor')
                    ->latest('id')
            )
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('لا توجد عمليات مسجلة حتى الآن')
            ->emptyStateDescription(
                'ستظهر هنا العمليات والتعديلات الإدارية المسجلة في النظام.'
            )
            ->columns([
                TextColumn::make('actor.name')
                    ->label('المستخدم')
                    ->placeholder('النظام'),

                TextColumn::make('action')
                    ->label('العملية')
                    ->searchable(),

                TextColumn::make('entity_type')
                    ->label('نوع السجل')
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            $state
                                ? class_basename($state)
                                : '—'
                    ),

                TextColumn::make('entity_id')
                    ->label('رقم السجل')
                    ->placeholder('—'),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('التفاصيل')
                    ->url(
                        fn (AuditLog $record): string =>
                            AuditLogResource::getUrl(
                                'view',
                                ['record' => $record]
                            )
                    ),
            ]);
    }
}