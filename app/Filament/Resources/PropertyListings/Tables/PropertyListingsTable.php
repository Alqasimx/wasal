<?php

namespace App\Filament\Resources\PropertyListings\Tables;

use App\Models\PropertyListing;
use App\Services\AuditService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class PropertyListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('property_image')
                    ->label('الصورة')
                    ->state(fn (PropertyListing $record): ?string => $record->property?->gallery[0] ?? null)
                    ->disk('public')
                    ->height(96)
                    ->width(136),

                TextColumn::make('listing_number')
                    ->label('رقم الإعلان')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('public_title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(26)
                    ->wrap(),

                TextColumn::make('property.internal_code')
                    ->label('العقار')
                    ->placeholder('—'),

                TextColumn::make('purpose')
                    ->label('الغرض')
                    ->formatStateUsing(fn (string $state): string => $state === 'rent' ? 'إيجار' : 'بيع')
                    ->badge(),

                TextColumn::make('price')
                    ->label('السعر')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyListing::STATUS_DRAFT => 'مسودة',
                        PropertyListing::STATUS_PENDING_REVIEW => 'قيد المراجعة',
                        PropertyListing::STATUS_CHANGES_REQUESTED => 'مطلوب تعديل',
                        PropertyListing::STATUS_APPROVED => 'معتمد',
                        PropertyListing::STATUS_PUBLISHED => 'منشور',
                        PropertyListing::STATUS_PAUSED => 'موقوف',
                        PropertyListing::STATUS_REJECTED => 'مرفوض',
                        PropertyListing::STATUS_EXPIRED => 'منتهي',
                        PropertyListing::STATUS_SOLD => 'تم البيع',
                        PropertyListing::STATUS_RENTED => 'تم التأجير',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),

                TextColumn::make('property.city.name_ar')
                    ->label('المدينة')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('property.district.name_ar')
                    ->label('المديرية')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('price_period')
                    ->label('الفترة')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('share_enabled')
                    ->label('مشاركة')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('share_count')
                    ->label('الزيارات')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('share_url')
                    ->label('رابط المشاركة')
                    ->state(fn (PropertyListing $record): ?string => $record->share_enabled && $record->share_token
                        ? url('/api/v1/shared-offers/'.$record->share_token)
                        : null)
                    ->copyable()
                    ->limit(28)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        PropertyListing::STATUS_DRAFT => 'مسودة',
                        PropertyListing::STATUS_PUBLISHED => 'منشور',
                        PropertyListing::STATUS_PAUSED => 'موقوف',
                        PropertyListing::STATUS_EXPIRED => 'منتهي',
                        PropertyListing::STATUS_SOLD => 'تم البيع',
                        PropertyListing::STATUS_RENTED => 'تم التأجير',
                    ]),

                SelectFilter::make('purpose')
                    ->label('الغرض')
                    ->options([
                        'sale' => 'بيع',
                        'rent' => 'إيجار',
                    ]),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->recordActions([
                EditAction::make()
                    ->label('تعديل'),

                self::publishAction(),
                self::pauseAction(),
            ]);
    }

    private static function publishAction(): Action
    {
        return Action::make('publish')
            ->label('نشر')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (PropertyListing $record): bool =>
                auth()->user()?->can('property_listings.manage')
                && ! in_array($record->status, [
                    PropertyListing::STATUS_PUBLISHED,
                    PropertyListing::STATUS_SOLD,
                    PropertyListing::STATUS_RENTED,
                ], true)
            )
            ->action(function (PropertyListing $record): void {
                DB::transaction(function () use ($record): void {
                    $oldValues = [
                        'status' => $record->status,
                        'published_at' => $record->published_at,
                    ];

                    $record->update([
                        'status' => PropertyListing::STATUS_PUBLISHED,
                        'published_at' => $record->published_at ?? now(),
                    ]);

                    app(AuditService::class)->forModel(
                        action: 'property_listing.published',
                        model: $record,
                        oldValues: $oldValues,
                        newValues: [
                            'status' => $record->fresh()->status,
                            'published_at' => $record->fresh()->published_at,
                        ],
                        actor: auth()->user(),
                        request: request(),
                    );
                });

                Notification::make()
                    ->success()
                    ->title('تم نشر الإعلان')
                    ->send();
            });
    }

    private static function pauseAction(): Action
    {
        return Action::make('pause')
            ->label('إيقاف')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (PropertyListing $record): bool =>
                auth()->user()?->can('property_listings.manage')
                && $record->status === PropertyListing::STATUS_PUBLISHED
            )
            ->action(function (PropertyListing $record): void {
                DB::transaction(function () use ($record): void {
                    $oldStatus = $record->status;

                    $record->update([
                        'status' => PropertyListing::STATUS_PAUSED,
                    ]);

                    app(AuditService::class)->forModel(
                        action: 'property_listing.paused',
                        model: $record,
                        oldValues: [
                            'status' => $oldStatus,
                        ],
                        newValues: [
                            'status' => $record->fresh()->status,
                        ],
                        actor: auth()->user(),
                        request: request(),
                    );
                });

                Notification::make()
                    ->success()
                    ->title('تم إيقاف الإعلان')
                    ->send();
            });
    }
}
