<?php

namespace App\Filament\Resources\PropertyListings\Tables;

use App\Models\PropertyListing;
use App\Services\PropertyListingWorkflowService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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

                TextColumn::make('reviewer.name')
                    ->label('المراجع')
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
                        PropertyListing::STATUS_PENDING_REVIEW => 'قيد المراجعة',
                        PropertyListing::STATUS_CHANGES_REQUESTED => 'مطلوب تعديل',
                        PropertyListing::STATUS_PUBLISHED => 'منشور',
                        PropertyListing::STATUS_REJECTED => 'مرفوض',
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
                    ->label('تعديل')
                    ->visible(fn (PropertyListing $record): bool => self::canEdit($record)),

                self::submitForReviewAction(),
                self::approveAction(),
                self::requestChangesAction(),
                self::rejectAction(),
            ]);
    }

    private static function canManage(): bool
    {
        return (bool) auth()->user()?->can('property_listings.manage');
    }

    private static function canReview(): bool
    {
        return (bool) auth()->user()?->can('property_listings.review');
    }

    private static function canPublish(): bool
    {
        return (bool) auth()->user()?->can('property_listings.publish');
    }

    private static function canEdit(PropertyListing $record): bool
    {
        if (! self::canManage()) {
            return false;
        }

        if ($record->status === PropertyListing::STATUS_PENDING_REVIEW) {
            return false;
        }

        if (
            $record->status === PropertyListing::STATUS_PUBLISHED
            && app(PropertyListingWorkflowService::class)->hasPendingVersion($record)
        ) {
            return false;
        }

        return true;
    }

    private static function submitForReviewAction(): Action
    {
        return Action::make('submitForReview')
            ->label('إرسال للمراجعة')
            ->color('warning')
            ->visible(function (PropertyListing $record): bool {
                if (! self::canManage()) {
                    return false;
                }

                $workflow = app(PropertyListingWorkflowService::class);

                if ($record->status === PropertyListing::STATUS_PUBLISHED) {
                    return ! $workflow->hasPendingVersion($record)
                        && $workflow->hasEditableRevision($record);
                }

                return in_array($record->status, [
                    PropertyListing::STATUS_DRAFT,
                    PropertyListing::STATUS_CHANGES_REQUESTED,
                ], true);
            })
            ->requiresConfirmation()
            ->action(function (PropertyListing $record): void {
                app(PropertyListingWorkflowService::class)->submitForReview(
                    listing: $record,
                    actor: auth()->user(),
                    request: request(),
                );

                Notification::make()
                    ->success()
                    ->title('تم إرسال الإعلان للمراجعة')
                    ->send();
            });
    }

    private static function approveAction(): Action
    {
        return Action::make('approveAndPublish')
            ->label('اعتماد ونشر')
            ->color('success')
            ->form([
                Textarea::make('review_notes')
                    ->label('ملاحظات الاعتماد')
                    ->rows(3),
            ])
            ->visible(fn (PropertyListing $record): bool => self::canPublish()
                && app(PropertyListingWorkflowService::class)->hasPendingVersion($record))
            ->action(function (PropertyListing $record, array $data): void {
                app(PropertyListingWorkflowService::class)->approveAndPublish(
                    listing: $record,
                    actor: auth()->user(),
                    reviewNotes: $data['review_notes'] ?? null,
                    request: request(),
                );

                Notification::make()
                    ->success()
                    ->title('تم اعتماد الإعلان ونشر النسخة المعتمدة')
                    ->send();
            });
    }

    private static function requestChangesAction(): Action
    {
        return Action::make('requestChanges')
            ->label('طلب تعديلات')
            ->color('warning')
            ->form([
                Textarea::make('review_notes')
                    ->label('سبب طلب التعديلات')
                    ->required()
                    ->rows(4),
            ])
            ->visible(fn (PropertyListing $record): bool => self::canReview()
                && app(PropertyListingWorkflowService::class)->hasPendingVersion($record))
            ->action(function (PropertyListing $record, array $data): void {
                app(PropertyListingWorkflowService::class)->requestChanges(
                    listing: $record,
                    actor: auth()->user(),
                    reviewNotes: $data['review_notes'],
                    request: request(),
                );

                Notification::make()
                    ->success()
                    ->title('تم إرسال طلب التعديلات')
                    ->send();
            });
    }

    private static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('رفض الإعلان')
            ->color('danger')
            ->requiresConfirmation()
            ->form([
                Textarea::make('review_notes')
                    ->label('سبب الرفض')
                    ->required()
                    ->rows(4),
            ])
            ->visible(fn (PropertyListing $record): bool => self::canReview()
                && app(PropertyListingWorkflowService::class)->hasPendingVersion($record))
            ->action(function (PropertyListing $record, array $data): void {
                app(PropertyListingWorkflowService::class)->reject(
                    listing: $record,
                    actor: auth()->user(),
                    reviewNotes: $data['review_notes'],
                    request: request(),
                );

                Notification::make()
                    ->success()
                    ->title('تم رفض النسخة المرسلة للمراجعة')
                    ->send();
            });
    }
}
