<x-filament-panels::page>
    @php
        $kpis = $report['kpis'] ?? [];
        $collections = $report['collections'] ?? [];
        $expenses = $report['expenses'] ?? [];
        $outstanding = $report['outstanding'] ?? [];
        $maintenance = $report['maintenance_by_status'] ?? [];
        $from = $report['period']['from'] ?? now()->startOfMonth()->toDateString();
        $to = $report['period']['to'] ?? today()->toDateString();

        $exportUrl = fn (string $type): string => route(
            'property-management.reports.export',
            ['report' => $type, 'from' => $from, 'to' => $to],
        );
    @endphp

    <div class="grid gap-6">
        <section class="fi-section p-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold">تقرير إدارة الأملاك</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        الفترة الحالية: {{ $from }} إلى {{ $to }}
                    </p>
                </div>

                @if (auth()->user()?->can('property_management_reports.export'))
                    <div class="flex flex-wrap gap-2">
                        <a class="fi-btn fi-btn-color-gray" href="{{ $exportUrl('collections') }}">تصدير التحصيلات CSV</a>
                        <a class="fi-btn fi-btn-color-gray" href="{{ $exportUrl('arrears') }}">تصدير المتأخرات CSV</a>
                        <a class="fi-btn fi-btn-color-gray" href="{{ $exportUrl('expenses') }}">تصدير المصروفات CSV</a>
                        <a class="fi-btn fi-btn-color-gray" href="{{ $exportUrl('maintenance') }}">تصدير الصيانة CSV</a>
                        <a class="fi-btn fi-btn-color-primary" href="{{ $exportUrl('owner-settlements') }}">تصدير تسويات الملاك CSV</a>
                    </div>
                @endif
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['العقارات المُدارة', $kpis['managed_properties'] ?? 0],
                ['إجمالي الوحدات', $kpis['total_units'] ?? 0],
                ['الوحدات المشغولة', $kpis['occupied_units'] ?? 0],
                ['الوحدات الشاغرة', $kpis['vacant_units'] ?? 0],
                ['نسبة الإشغال', ($kpis['occupancy_rate'] ?? 0).'%'],
                ['العقود النشطة', $kpis['active_tenancies'] ?? 0],
                ['الاستحقاقات المتأخرة', $kpis['overdue_dues'] ?? 0],
                ['الصيانة المفتوحة', $kpis['open_maintenance'] ?? 0],
                ['مستندات تنتهي قريبًا', $kpis['expiring_documents'] ?? 0],
                ['اتفاقات إدارة تنتهي قريبًا', $kpis['expiring_agreements'] ?? 0],
                ['تسويات ملاك معلقة', $kpis['pending_owner_settlements'] ?? 0],
            ] as [$label, $value])
                <div class="fi-wi-stats-overview-stat p-5">
                    <div class="text-sm text-gray-500">{{ $label }}</div>
                    <div class="mt-2 text-3xl font-black">{{ $value }}</div>
                </div>
            @endforeach
        </section>

        <section class="grid gap-5 lg:grid-cols-3">
            <div class="fi-section p-5">
                <h3 class="text-lg font-bold">التحصيلات حسب العملة</h3>
                <div class="mt-4 grid gap-2">
                    @forelse ($collections as $item)
                        <div class="flex justify-between gap-4 border-b border-gray-200 py-2 last:border-0">
                            <span>{{ $item['currency'] }}</span>
                            <strong>{{ number_format($item['total'], 2) }}</strong>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد تحصيلات خلال الفترة.</p>
                    @endforelse
                </div>
            </div>

            <div class="fi-section p-5">
                <h3 class="text-lg font-bold">المصروفات حسب العملة</h3>
                <div class="mt-4 grid gap-2">
                    @forelse ($expenses as $item)
                        <div class="flex justify-between gap-4 border-b border-gray-200 py-2 last:border-0">
                            <span>{{ $item['currency'] }}</span>
                            <strong>{{ number_format($item['total'], 2) }}</strong>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد مصروفات خلال الفترة.</p>
                    @endforelse
                </div>
            </div>

            <div class="fi-section p-5">
                <h3 class="text-lg font-bold">الأرصدة المستحقة حسب العملة</h3>
                <div class="mt-4 grid gap-2">
                    @forelse ($outstanding as $item)
                        <div class="flex justify-between gap-4 border-b border-gray-200 py-2 last:border-0">
                            <span>{{ $item['currency'] }}</span>
                            <strong>{{ number_format($item['total'], 2) }}</strong>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد أرصدة مستحقة.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="fi-section p-5">
            <h3 class="text-lg font-bold">توزيع طلبات الصيانة حسب الحالة</h3>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                @foreach ([
                    'open' => 'مفتوحة',
                    'scheduled' => 'مجدولة',
                    'in_progress' => 'قيد التنفيذ',
                    'on_hold' => 'معلقة',
                    'completed' => 'مكتملة',
                    'cancelled' => 'ملغاة',
                ] as $status => $label)
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">{{ $label }}</div>
                        <div class="mt-1 text-2xl font-bold">{{ $maintenance[$status] ?? 0 }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="fi-section p-5">
            <h3 class="text-lg font-bold">قواعد المؤشرات المالية</h3>
            <p class="mt-2 text-sm text-gray-500">
                لا يتم جمع عملات مختلفة في رقم واحد. التحصيلات والمصروفات والمتأخرات تعرض منفصلة حسب العملة حتى تظل التقارير المالية صحيحة.
            </p>
        </section>
    </div>
</x-filament-panels::page>
