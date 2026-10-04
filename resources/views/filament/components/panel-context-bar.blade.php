@php
    $user = auth()->user();
    $currentPanel = \Filament\Facades\Filament::getCurrentPanel()?->getId();

    $panels = [
        'admin' => [
            'label' => 'إدارة النظام',
            'url' => url('/admin'),
            'allowed' => $user && ($user->hasRole('system_admin') || $user->can('panels.admin.access')),
        ],
        'real-estate' => [
            'label' => 'إدارة العقارات',
            'url' => url('/real-estate'),
            'allowed' => $user && ($user->hasRole('system_admin') || $user->can('panels.real_estate.access')),
        ],
        'property-management' => [
            'label' => 'إدارة الأملاك',
            'url' => url('/property-management'),
            'allowed' => $user && ($user->hasRole('system_admin') || $user->can('panels.property_management.access')),
        ],
    ];

    $current = $panels[$currentPanel] ?? [
        'label' => 'وصال',
        'url' => url('/'),
        'allowed' => true,
    ];
@endphp

<div class="wasal-page-context" dir="rtl">
    <div class="wasal-page-context__identity">
        <span>القسم الحالي</span>
        <strong>{{ $current['label'] }}</strong>
    </div>

    <div class="wasal-page-context__actions">
        <button
            type="button"
            class="wasal-page-context__button wasal-page-context__button--back"
            onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = @js($current['url']); }"
        >
            ← الصفحة السابقة
        </button>

        <a
            href="{{ $current['url'] }}"
            class="wasal-page-context__button wasal-page-context__button--home"
        >
            لوحة {{ $current['label'] }}
        </a>

        @foreach ($panels as $panelId => $panel)
            @if ($panel['allowed'])
                <a
                    href="{{ $panel['url'] }}"
                    class="wasal-page-context__panel {{ $currentPanel === $panelId ? 'wasal-page-context__panel--active' : '' }}"
                    @if ($currentPanel === $panelId) aria-current="page" @endif
                >
                    {{ $panel['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>
