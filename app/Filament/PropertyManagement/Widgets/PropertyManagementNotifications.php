<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Models\Notification as WasalNotification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PropertyManagementNotifications extends TableWidget
{
    protected static ?string $heading = 'الإشعارات';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getNotificationQuery(): Builder
    {
        return WasalNotification::query()
            ->where('user_id', auth()->id())
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->latest('created_at');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getNotificationQuery())
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('لا توجد إشعارات')
            ->emptyStateDescription('ستظهر هنا تنبيهات الصيانة والاستحقاقات والمهام القريبة.')
            ->columns([
                TextColumn::make('title')
                    ->label('الإشعار')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('body')
                    ->label('التفاصيل')
                    ->wrap()
                    ->limit(70),

                TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'maintenance_due' => 'صيانة',
                        'rent_due' => 'استحقاق إيجار',
                        'task_due_soon' => 'مهمة',
                        default => 'تنبيه',
                    })
                    ->badge(),

                TextColumn::make('read_at')
                    ->label('الحالة')
                    ->state(fn (WasalNotification $record): string => $record->read_at ? 'مقروء' : 'جديد')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'جديد' ? 'warning' : 'gray'),

                TextColumn::make('created_at')
                    ->label('وقت الإشعار')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ]);
    }
}
