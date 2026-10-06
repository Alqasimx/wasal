<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Filament\PropertyManagement\Resources\RentPayments\RentPaymentResource;
use App\Models\RentDueItem;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RentArrearsWidget extends TableWidget
{
    protected static ?string $heading = 'المتأخرات والتحصيل';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('rent_due_items.view') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RentDueItem::query()
                    ->with(['tenancy.tenant', 'tenancy.unit.property', 'currency'])
                    ->whereIn('status', [
                        RentDueItem::STATUS_DUE,
                        RentDueItem::STATUS_PARTIAL,
                        RentDueItem::STATUS_OVERDUE,
                    ])
                    ->whereDate('due_date', '<=', today())
                    ->whereColumn('paid_amount', '<', 'amount')
                    ->orderBy('due_date')
            )
            ->defaultPaginationPageOption(8)
            ->emptyStateHeading('لا توجد متأخرات حالية')
            ->emptyStateDescription('عند وجود استحقاقات غير مسددة ستظهر هنا للمتابعة والتحصيل.')
            ->columns([
                TextColumn::make('tenancy.tenant.name')
                    ->label('المستأجر')
                    ->searchable(),

                TextColumn::make('tenancy.unit.property.internal_code')
                    ->label('العقار')
                    ->placeholder('—'),

                TextColumn::make('tenancy.unit.code')
                    ->label('الوحدة')
                    ->placeholder('—'),

                TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('remaining')
                    ->label('المتبقي')
                    ->state(fn (RentDueItem $record): float => max(
                        0,
                        (float) $record->amount - (float) $record->paid_amount
                    ))
                    ->numeric(),

                TextColumn::make('currency.code')
                    ->label('العملة'),

                TextColumn::make('late_days')
                    ->label('أيام التأخير')
                    ->state(fn (RentDueItem $record): int =>
                        $record->due_date->lt(today())
                            ? $record->due_date->diffInDays(today())
                            : 0)
                    ->badge(),
            ])
            ->recordActions([
                Action::make('collect')
                    ->label('تحصيل')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (): bool => auth()->user()?->can('rent_payments.manage') ?? false)
                    ->url(fn (RentDueItem $record): string => RentPaymentResource::getUrl(
                        'create',
                        ['rent_due_item_id' => $record->id],
                        panel: 'property-management',
                    )),
            ]);
    }
}
