@php
    $user = auth()->user();
    $canAdmin = $user && ($user->hasRole('system_admin') || $user->can('panels.admin.access'));
    $canRealEstate = $user && ($user->hasRole('system_admin') || $user->can('panels.real_estate.access'));
    $canPropertyManagement = $user && ($user->hasRole('system_admin') || $user->can('panels.property_management.access'));
@endphp

<x-filament-widgets::widget>
    <div class="wasal-panel-switcher">
        <div class="wasal-panel-switcher__copy">
            <strong>واجهات وصال</strong>
            <span>انتقل مباشرة إلى القسم الذي تريد العمل عليه.</span>
        </div>

        <div class="wasal-panel-switcher__actions">
            @if ($canAdmin)
                <a href="{{ url('/admin') }}" class="wasal-panel-switcher__button">
                    إدارة النظام
                </a>
            @endif

            @if ($canRealEstate)
                <a href="{{ url('/real-estate') }}" class="wasal-panel-switcher__button wasal-panel-switcher__button--primary">
                    إدارة العقارات
                </a>
            @endif

            @if ($canPropertyManagement)
                <a href="{{ url('/property-management') }}" class="wasal-panel-switcher__button">
                    إدارة الأملاك
                </a>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
