<?php

namespace App\Filament\Resources\PropertyUnits\Tables;

use App\Models\PropertyUnit;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PropertyUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('property_image')
                    ->label('الصورة')
                    ->state(fn ($record): ?string => $record->property?->gallery[0] ?? null)
                    ->disk('public')
                    ->height(96)
                    ->width(136),

                TextColumn::make('property.internal_code')
                    ->label('العقار')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('code')
                    ->label('رمز الوحدة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('الوحدة')
                    ->searchable()
                    ->limit(24),

                TextColumn::make('occupancy')
                    ->label('الإشغال')
                    ->state(fn (PropertyUnit $record): string =>
                        $record->isOccupied() ? 'occupied' : 'vacant')
                    ->formatStateUsing(fn (string $state): string =>
                        $state === 'occupied' ? 'مشغولة' : 'شاغرة')
                    ->badge()
                    ->color(fn (string $state): string =>
                        $state === 'occupied' ? 'success' : 'warning'),

                TextColumn::make('current_tenant')
                    ->label('المستأجر الحالي')
                    ->state(fn (PropertyUnit $record): string =>
                        $record->currentTenancy?->tenant?->name ?? '—')
                    ->searchable(false),

                TextColumn::make('contract_end')
                    ->label('نهاية العقد')
                    ->state(fn (PropertyUnit $record) => $record->currentTenancy?->ends_at)
                    ->date('Y-m-d')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('الحالة التشغيلية')
                    ->state(fn (PropertyUnit $record): string => $record->effectiveOperationalStatus())
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyUnit::STATUS_MAINTENANCE => 'تحت الصيانة',
                        PropertyUnit::STATUS_UNAVAILABLE => 'غير متاحة',
                        PropertyUnit::STATUS_OCCUPIED => 'مشغولة',
                        default => 'متاحة',
                    })
                    ->badge()
                    ->sortable(),

                TextColumn::make('floor_number')
                    ->label('الطابق')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('unit_type')
                    ->label('النوع')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bedrooms')
                    ->label('الغرف')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bathrooms')
                    ->label('الحمامات')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('area')
                    ->label('المساحة')
                    ->suffix(' م²')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('occupancy')
                    ->label('الإشغال')
                    ->options([
                        'occupied' => 'مشغولة',
                        'vacant' => 'شاغرة',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'occupied' => $query->occupied(),
                            'vacant' => $query->vacant(),
                            default => $query,
                        };
                    }),

                SelectFilter::make('status')
                    ->label('الحالة التشغيلية')
                    ->options([
                        PropertyUnit::STATUS_AVAILABLE => 'متاحة',
                        PropertyUnit::STATUS_MAINTENANCE => 'تحت الصيانة',
                        PropertyUnit::STATUS_UNAVAILABLE => 'غير متاحة',
                    ]),

                SelectFilter::make('property_id')
                    ->label('العقار')
                    ->relationship('property', 'internal_code')
                    ->searchable()
                    ->preload(),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()
                    ->label('تعديل'),
            ]);
    }
}
