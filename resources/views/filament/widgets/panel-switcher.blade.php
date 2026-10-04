@php
    $user = auth()->user();
    $currentPanel = \Filament\Facades\Filament::getCurrentPanel()?->getId();

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
                <a
                    href="{{ url('/admin') }}"
                    class="wasal-panel-switcher__button {{ $currentPanel === 'admin' ? 'wasal-panel-switcher__button--primary' : '' }}"
                    @if ($currentPanel === 'admin') aria-current="page" @endif
                >
                    إدارة النظام
                </a>
            @endif

            @if ($canRealEstate)
                <a
                    href="{{ url('/real-estate') }}"
                    class="wasal-panel-switcher__button {{ $currentPanel === 'real-estate' ? 'wasal-panel-switcher__button--primary' : '' }}"
                    @if ($currentPanel === 'real-estate') aria-current="page" @endif
                >
                    إدارة العقارات
                </a>
            @endif

            @if ($canPropertyManagement)
                <a
                    href="{{ url('/property-management') }}"
                    class="wasal-panel-switcher__button {{ $currentPanel === 'property-management' ? 'wasal-panel-switcher__button--primary' : '' }}"
                    @if ($currentPanel === 'property-management') aria-current="page" @endif
                >
                    إدارة الأملاك
                </a>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
