<?php

namespace App\Filament\Resources\Tenancies\Tables;

use App\Models\Tenancy;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TenanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('unit.property.internal_code')
                    ->label('العقار')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('tenant.name')
                    ->label('المستأجر')
                    ->searchable(),

                TextColumn::make('rent_amount')
                    ->label('الإيجار')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('currency.code')
                    ->label('العملة')
                    ->placeholder('—'),

                TextColumn::make('payment_frequency')
                    ->label('الدورية')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Tenancy::FREQUENCY_QUARTERLY => 'كل 3 أشهر',
                        Tenancy::FREQUENCY_SEMIANNUAL => 'كل 6 أشهر',
                        Tenancy::FREQUENCY_ANNUAL => 'سنوي',
                        default => 'شهري',
                    })
                    ->badge(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Tenancy::STATUS_ENDED => 'منتهي',
                        Tenancy::STATUS_CANCELLED => 'ملغي',
                        default => 'نشط',
                    })
                    ->badge()
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('البداية')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ends_at')
                    ->label('النهاية')
                    ->date('Y-m-d')
                    ->placeholder('مفتوح')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        Tenancy::STATUS_ACTIVE => 'نشط',
                        Tenancy::STATUS_ENDED => 'منتهي',
                        Tenancy::STATUS_CANCELLED => 'ملغي',
                    ]),

                SelectFilter::make('payment_frequency')
                    ->label('دورية السداد')
                    ->options([
                        Tenancy::FREQUENCY_MONTHLY => 'شهري',
                        Tenancy::FREQUENCY_QUARTERLY => 'كل 3 أشهر',
                        Tenancy::FREQUENCY_SEMIANNUAL => 'كل 6 أشهر',
                        Tenancy::FREQUENCY_ANNUAL => 'سنوي',
                    ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ]);
    }
}
