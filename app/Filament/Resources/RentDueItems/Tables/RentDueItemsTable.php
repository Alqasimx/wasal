<?php

namespace App\Filament\Resources\RentDueItems\Tables;

use App\Models\RentDueItem;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentDueItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'desc')
            ->columns([
                TextColumn::make('tenancy.tenant.name')
                    ->label('المستأجر')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('tenancy.unit.property.internal_code')
                    ->label('العقار')
                    ->placeholder('—'),

                TextColumn::make('tenancy.unit.code')
                    ->label('الوحدة')
                    ->placeholder('—'),

                TextColumn::make('due_date')
                    ->label('الاستحقاق')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('المستحق')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('المسدد')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->state(fn (RentDueItem $record): float => max(
                        0,
                        (float) $record->amount - (float) $record->paid_amount
                    ))
                    ->numeric(),

                TextColumn::make('currency.code')
                    ->label('العملة')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        RentDueItem::STATUS_PARTIAL => 'مسدد جزئيًا',
                        RentDueItem::STATUS_PAID => 'مسدد',
                        RentDueItem::STATUS_OVERDUE => 'متأخر',
                        RentDueItem::STATUS_CANCELLED => 'ملغي',
                        default => 'مستحق',
                    })
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        RentDueItem::STATUS_DUE => 'مستحق',
                        RentDueItem::STATUS_PARTIAL => 'مسدد جزئيًا',
                        RentDueItem::STATUS_PAID => 'مسدد',
                        RentDueItem::STATUS_OVERDUE => 'متأخر',
                        RentDueItem::STATUS_CANCELLED => 'ملغي',
                    ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ]);
    }
}
