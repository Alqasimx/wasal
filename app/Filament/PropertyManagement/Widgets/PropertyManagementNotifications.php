<?php

namespace App\Filament\PropertyManagement\Widgets;

use App\Filament\Resources\Tasks\TaskResource;
use App\Filament\Resources\PropertyUnits\PropertyUnitResource;
use App\Filament\Resources\RentDueItems\RentDueItemResource;
use App\Filament\Resources\Tenancies\TenancyResource;
use App\Filament\PropertyManagement\Resources\MaintenanceRequests\MaintenanceRequestResource;
use App\Filament\PropertyManagement\Resources\OwnerSettlements\OwnerSettlementResource;
use App\Filament\PropertyManagement\Resources\PropertyDocuments\PropertyDocumentResource;
use App\Filament\PropertyManagement\Resources\PropertyExpenses\PropertyExpenseResource;
use App\Filament\PropertyManagement\Resources\PropertyInspections\PropertyInspectionResource;
use App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\PropertyManagementAgreementResource;
use App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\PropertyServiceScheduleResource;
use App\Filament\PropertyManagement\Resources\PropertyVendors\PropertyVendorResource;
use App\Models\Notification as WasalNotification;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PropertyManagementNotifications extends TableWidget
{
    protected static ?string $heading = 'الإشعارات';

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '10s';

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
                        'maintenance_urgent' => 'صيانة عاجلة',
                        'rent_due' => 'استحقاق إيجار',
                        'service_due' => 'خدمة مجدولة',
                        'tenancy_due' => 'عقد إيجار',
                        'agreement_due' => 'اتفاق إدارة',
                        'inspection_due' => 'معاينة عقار',
                        'document_due' => 'مستند منتهي قريبًا',
                        'expense_due' => 'مصروف',
                        'owner_settlement_due' => 'تسوية مالك',
                        'occupancy_due' => 'إشغال وحدة',
                        'vendor_due' => 'مورد / فني',
                        'task_due_soon' => 'مهمة',
                        default => 'تنبيه',
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'صيانة عاجلة' ? 'danger' : 'info'),

                TextColumn::make('data.priority')
                    ->label('الأولوية')
                    ->state(fn (WasalNotification $record): string => match ($record->data['priority'] ?? null) {
                        'urgent' => 'عاجلة',
                        'high' => 'عالية',
                        'low' => 'منخفضة',
                        default => 'عادية',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'عاجلة' => 'danger',
                        'عالية' => 'warning',
                        'منخفضة' => 'gray',
                        default => 'info',
                    }),

                TextColumn::make('read_at')
                    ->label('الحالة')
                    ->state(fn (WasalNotification $record): string => $record->read_at ? 'مقروء' : 'جديد')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'جديد' ? 'warning' : 'success'),

                TextColumn::make('created_at')
                    ->label('وقت الإشعار')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('open_task')
                    ->label('فتح المهمة')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->visible(fn (WasalNotification $record): bool => filled($record->data['task_id'] ?? null))
                    ->url(fn (WasalNotification $record): string => TaskResource::getUrl(
                        'edit',
                        ['record' => $record->data['task_id']],
                        panel: 'property-management',
                    )),

                Action::make('open_related_record')
                    ->label('فتح السجل المرتبط')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->visible(fn (WasalNotification $record): bool => $this->relatedRecordUrl($record) !== null)
                    ->url(fn (WasalNotification $record): ?string => $this->relatedRecordUrl($record)),

                Action::make('mark_read')
                    ->label('تمت القراءة')
                    ->icon('heroicon-o-check')
                    ->visible(fn (WasalNotification $record): bool => $record->read_at === null)
                    ->action(fn (WasalNotification $record) =>
                        $record->update(['read_at' => now()])),
            ]);
    }

    protected function relatedRecordUrl(WasalNotification $record): ?string
    {
        $relatedId = $record->data['related_id'] ?? null;

        if (! $relatedId) {
            return null;
        }

        if (($record->data['related_type'] ?? null) === 'owner_settlement') {
            return OwnerSettlementResource::getUrl('index', panel: 'property-management');
        }

        $resource = match ($record->data['related_type'] ?? null) {
            'rent_due_item' => RentDueItemResource::class,
            'maintenance_request' => MaintenanceRequestResource::class,
            'property_service_schedule' => PropertyServiceScheduleResource::class,
            'tenancy' => TenancyResource::class,
            'property_management_agreement' => PropertyManagementAgreementResource::class,
            'property_inspection' => PropertyInspectionResource::class,
            'property_document' => PropertyDocumentResource::class,
            'property_expense' => PropertyExpenseResource::class,
            'property_unit' => PropertyUnitResource::class,
            'property_vendor' => PropertyVendorResource::class,
            default => null,
        };

        return $resource
            ? $resource::getUrl('edit', ['record' => $relatedId], panel: 'property-management')
            : null;
    }
}
