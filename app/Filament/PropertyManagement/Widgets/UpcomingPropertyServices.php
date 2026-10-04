<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\PropertyServiceScheduleResource;
use App\Models\PropertyService;
use App\Models\PropertyServiceSchedule;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingPropertyServices extends TableWidget
{
    protected static ?string $heading = 'جدول الخدمات القادمة';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('property_service_schedules.view') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PropertyServiceSchedule::query()
                    ->with(['service', 'property', 'unit', 'vendor'])
                    ->where('is_active', true)
                    ->whereNotNull('next_due_at')
                    ->where('next_due_at', '<=', now()->copy()->addDays(30))
                    ->orderBy('next_due_at')
            )
            ->defaultPaginationPageOption(8)
            ->emptyStateHeading('لا توجد خدمات مجدولة قريبًا')
            ->emptyStateDescription('يمكن إضافة صيانة دورية أو تنظيف أو فحص من قسم جدولة الخدمات.')
            ->columns([
                TextColumn::make('service.name_ar')
                    ->label('الخدمة'),

                TextColumn::make('property.internal_code')
                    ->label('العقار'),

                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->placeholder('كامل العقار'),

                TextColumn::make('vendor.name')
                    ->label('الفني / المورد')
                    ->placeholder('غير معين'),

                TextColumn::make('next_due_at')
                    ->label('الموعد القادم')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('frequency')
                    ->label('التكرار')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PropertyService::FREQUENCY_DAILY => 'يومي',
                        PropertyService::FREQUENCY_WEEKLY => 'أسبوعي',
                        PropertyService::FREQUENCY_MONTHLY => 'شهري',
                        PropertyService::FREQUENCY_QUARTERLY => 'كل 3 أشهر',
                        PropertyService::FREQUENCY_SEMIANNUAL => 'كل 6 أشهر',
                        PropertyService::FREQUENCY_ANNUAL => 'سنوي',
                        PropertyService::FREQUENCY_CUSTOM_DAYS => 'أيام مخصصة',
                        default => 'مرة واحدة',
                    })
                    ->badge(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('فتح الجدول')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (PropertyServiceSchedule $record): string =>
                        PropertyServiceScheduleResource::getUrl(
                            'edit',
                            ['record' => $record],
                            panel: 'property-management',
                        )),
            ]);
    }
}
