<?php

namespace App\Filament\Resources\RentDueItems\Tables;

use App\Filament\PropertyManagement\Resources\RentPayments\RentPaymentResource;
use App\Models\RentDueItem;
use Filament\Actions\Action;
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

                TextColumn::make('overdue_days')
                    ->label('أيام التأخير')
                    ->state(function (RentDueItem $record): int {
                        if ($record->status === RentDueItem::STATUS_PAID || $record->due_date->gte(today())) {
                            return 0;
                        }

                        return $record->due_date->diffInDays(today());
                    })
                    ->badge(),

                TextColumn::make('currency.code')
                    ->label('العملة')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(function (string $state, RentDueItem $record): string {
                        if ($state === RentDueItem::STATUS_DUE && $record->due_date->isFuture()) {
                            return 'قادم';
                        }

                        return match ($state) {
                            RentDueItem::STATUS_PARTIAL => 'مسدد جزئيًا',
                            RentDueItem::STATUS_PAID => 'مسدد',
                            RentDueItem::STATUS_OVERDUE => 'متأخر',
                            RentDueItem::STATUS_CANCELLED => 'ملغي',
                            default => 'مستحق',
                        };
                    })
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        RentDueItem::STATUS_DUE => 'مستحق / قادم',
                        RentDueItem::STATUS_PARTIAL => 'مسدد جزئيًا',
                        RentDueItem::STATUS_PAID => 'مسدد',
                        RentDueItem::STATUS_OVERDUE => 'متأخر',
                        RentDueItem::STATUS_CANCELLED => 'ملغي',
                    ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                Action::make('collect')
                    ->label('تسجيل دفعة')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (RentDueItem $record): bool =>
                        $record->isDue()
                        && (auth()->user()?->can('rent_payments.manage') ?? false))
                    ->url(fn (RentDueItem $record): string => RentPaymentResource::getUrl(
                        'create',
                        ['rent_due_item_id' => $record->id],
                        panel: 'property-management',
                    )),

                EditAction::make()->label('تعديل'),
            ]);
    }
}
