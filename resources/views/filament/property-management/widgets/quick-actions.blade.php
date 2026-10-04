@php
    $user = auth()->user();
@endphp

<x-filament-widgets::widget>
    <div class="wasal-panel-switcher">
        <div class="wasal-panel-switcher__copy">
            <strong>إجراءات سريعة</strong>
            <span>ابدأ العملية اليومية مباشرة من لوحة إدارة الأملاك.</span>
        </div>

        <div class="wasal-panel-switcher__actions">
            @if ($user?->can('tenancies.manage'))
                <a href="{{ \App\Filament\Resources\Tenancies\TenancyResource::getUrl('create', panel: 'property-management') }}" class="wasal-panel-switcher__button">
                    عقد إيجار جديد
                </a>
            @endif

            @if ($user?->can('rent_payments.manage'))
                <a href="{{ \App\Filament\PropertyManagement\Resources\RentPayments\RentPaymentResource::getUrl('create', panel: 'property-management') }}" class="wasal-panel-switcher__button">
                    تسجيل تحصيل
                </a>
            @endif

            @if ($user?->can('maintenance_requests.manage'))
                <a href="{{ \App\Filament\PropertyManagement\Resources\MaintenanceRequests\MaintenanceRequestResource::getUrl('create', panel: 'property-management') }}" class="wasal-panel-switcher__button wasal-panel-switcher__button--primary">
                    طلب صيانة
                </a>
            @endif

            @if ($user?->can('property_service_schedules.manage'))
                <a href="{{ \App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\PropertyServiceScheduleResource::getUrl('create', panel: 'property-management') }}" class="wasal-panel-switcher__button">
                    جدولة خدمة
                </a>
            @endif

            @if ($user?->can('property_vendors.manage'))
                <a href="{{ \App\Filament\PropertyManagement\Resources\PropertyVendors\PropertyVendorResource::getUrl('create', panel: 'property-management') }}" class="wasal-panel-switcher__button">
                    إضافة فني / مورد
                </a>
            @endif

            @if ($user?->can('property_expenses.manage'))
                <a href="{{ \App\Filament\PropertyManagement\Resources\PropertyExpenses\PropertyExpenseResource::getUrl('create', panel: 'property-management') }}" class="wasal-panel-switcher__button">
                    إضافة مصروف
                </a>
            @endif

            @if ($user?->can('owner_settlements.manage'))
                <a href="{{ \App\Filament\PropertyManagement\Resources\OwnerSettlements\OwnerSettlementResource::getUrl('create', panel: 'property-management') }}" class="wasal-panel-switcher__button">
                    إنشاء تسوية مالك
                </a>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
