<?php

namespace App\Filament\Resources\PropertyRequests\Tables;

use App\Models\PropertyRequest;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropertyRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('المرجع')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->placeholder('—')
                    ->limit(24),

                TextColumn::make('purpose')
                    ->label('الغرض')
                    ->formatStateUsing(fn (string $state): string => $state === 'rent' ? 'إيجار' : 'شراء')
                    ->badge(),

                TextColumn::make('propertyType.name_ar')
                    ->label('النوع')
                    ->placeholder('—'),

                TextColumn::make('city.name_ar')
                    ->label('المدينة')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('assignedTo.name')
                    ->label('الموظف')
                    ->placeholder('غير معين')
                    ->limit(22),

                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        PropertyRequest::STATUS_NEW => 'جديد',
                        PropertyRequest::STATUS_CONTACTED => 'تم التواصل',
                        PropertyRequest::STATUS_SEARCHING => 'جارٍ البحث',
                        PropertyRequest::STATUS_MATCHED => 'تم العثور على خيارات',
                        PropertyRequest::STATUS_COMPLETED => 'مكتمل',
                        PropertyRequest::STATUS_CANCELLED => 'ملغى',
                    ]),

                SelectFilter::make('purpose')
                    ->label('الغرض')
                    ->options([
                        'sale' => 'شراء',
                        'rent' => 'إيجار',
                    ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()
                    ->label('تعديل'),
            ]);
    }
}
