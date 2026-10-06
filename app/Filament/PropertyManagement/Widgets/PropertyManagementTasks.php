<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PropertyManagementTasks extends TableWidget
{
    protected static ?string $heading = 'المهام والتنبيهات القادمة';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('tasks.view') ?? false;
    }

    protected function getTaskQuery(): Builder
    {
        return Task::query()
            ->with('assignee')
            ->whereNotIn('status', [
                Task::STATUS_COMPLETED,
                Task::STATUS_CANCELLED,
            ])
            ->where(function (Builder $query): void {
                $query
                    ->whereIn('related_type', [
                        'maintenance_request',
                        'property_service_schedule',
                        'rent_due_item',
                    ])
                    ->orWhereNull('related_type');
            })
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->orderBy('id');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTaskQuery())
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('لا توجد مهام مفتوحة')
            ->emptyStateDescription('ستظهر هنا مهام الصيانة والخدمات والتحصيل والمتابعات.')
            ->columns([
                TextColumn::make('title')
                    ->label('المهمة')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),

                TextColumn::make('assignee.name')
                    ->label('المسؤول')
                    ->placeholder('غير معين'),

                TextColumn::make('due_at')
                    ->label('الموعد')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('بدون موعد')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Task::STATUS_IN_PROGRESS => 'قيد التنفيذ',
                        default => 'جديدة',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'مكتملة' => 'success',
                        'قيد التنفيذ' => 'info',
                        default => 'warning',
                    }),

                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        Task::PRIORITY_URGENT => 'عاجلة',
                        Task::PRIORITY_HIGH => 'عالية',
                        Task::PRIORITY_LOW => 'منخفضة',
                        default => 'عادية',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'عاجلة' => 'danger',
                        'عالية' => 'warning',
                        'منخفضة' => 'gray',
                        default => 'info',
                    }),

                TextColumn::make('notification_state')
                    ->label('التنبيه')
                    ->state(function (Task $record): string {
                        if ($record->notified_at) {
                            return 'تم التنبيه';
                        }

                        if (! $record->due_at) {
                            return 'بدون موعد';
                        }

                        $notifyAt = $record->due_at->copy()->subMinutes($record->notify_before_minutes ?? 0);

                        return now()->greaterThanOrEqualTo($notifyAt)
                            ? 'جاهز للتنبيه'
                            : 'مجدول';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'تم التنبيه' => 'success',
                        'جاهز للتنبيه' => 'warning',
                        'مجدول' => 'info',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('فتح')
                    ->url(fn (Task $record): string => TaskResource::getUrl(
                        'edit',
                        ['record' => $record],
                        panel: 'property-management',
                    )),
            ]);
    }
}
