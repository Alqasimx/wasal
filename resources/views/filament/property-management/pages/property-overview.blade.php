<x-filament-panels::page>
    <div class="grid gap-6">
        <section class="fi-section p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="text-sm text-gray-500">{{ $property->internal_code }}</div>
                    <h2 class="mt-1 text-2xl font-bold">{{ $property->title_ar ?: 'عقار بدون اسم' }}</h2>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ $property->propertyType?->name_ar ?? 'نوع غير محدد' }}
                        @if ($property->city?->name_ar) · {{ $property->city->name_ar }} @endif
                        @if ($property->district?->name_ar) · {{ $property->district->name_ar }} @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a class="fi-btn fi-btn-color-primary" href="{{ \App\Filament\Resources\PropertyUnits\PropertyUnitResource::getUrl('index', panel: 'property-management') }}">الوحدات</a>
                    <a class="fi-btn fi-btn-color-gray" href="{{ \App\Filament\PropertyManagement\Resources\MaintenanceRequests\MaintenanceRequestResource::getUrl('index', panel: 'property-management') }}">الصيانة</a>
                    <a class="fi-btn fi-btn-color-gray" href="{{ \App\Filament\PropertyManagement\Resources\PropertyExpenses\PropertyExpenseResource::getUrl('index', panel: 'property-management') }}">المصروفات</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['الوحدات', $totalUnits, 'info'],
                ['المشغولة', $occupiedUnits, 'success'],
                ['الشاغرة', $vacantUnits, 'warning'],
                ['نسبة الإشغال', $occupancyRate.'%', 'primary'],
                ['الرصيد الإيجاري المستحق', number_format($outstandingRent, 2), 'danger'],
            ] as [$label, $value, $color])
                <div class="fi-wi-stats-overview-stat border-t-4 {{ match ($color) { 'success' => 'border-emerald-500', 'warning' => 'border-amber-500', 'danger' => 'border-red-500', default => 'border-sky-500' } }} p-5">
                    <div class="text-sm text-gray-500">{{ $label }}</div>
                    <div class="mt-2 text-2xl font-black">{{ $value }}</div>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="fi-section p-5">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-bold">اتفاقيات الإدارة</h3>
                    <span class="fi-badge fi-color-success">{{ $activeAgreements->count() }} نشطة</span>
                </div>
                <div class="mt-4 grid gap-3">
                    @forelse ($activeAgreements as $agreement)
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex justify-between gap-3">
                                <strong>{{ $agreement->agreement_number }}</strong>
                                <span>{{ $agreement->ends_at?->format('Y-m-d') ?? 'مفتوح' }}</span>
                            </div>
                            <div class="mt-2 text-sm text-gray-500">
                                المالك: {{ $agreement->propertyOwner?->external_owner_name ?: ($agreement->propertyOwner?->user?->name ?? 'غير محدد') }}
                                · المسؤول: {{ $agreement->manager?->name ?? 'غير معين' }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد اتفاقية إدارة نشطة.</p>
                    @endforelse
                </div>
            </div>

            <div class="fi-section p-5">
                <h3 class="text-lg font-bold">الوحدات والإشغال</h3>
                <div class="mt-4 grid gap-3">
                    @forelse ($property->units as $unit)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-700">
                            <div>
                                <strong>{{ $unit->code }}</strong>
                                <div class="text-sm text-gray-500">{{ $unit->currentTenancy?->tenant?->name ?? 'شاغرة' }}</div>
                            </div>
                            <span class="fi-badge {{ $unit->isOccupied() ? 'fi-color-success' : 'fi-color-warning' }}">
                                {{ $unit->isOccupied() ? 'مشغولة' : 'شاغرة' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد وحدات مسجلة.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="fi-section p-5">
                <h3 class="text-lg font-bold">الخدمات القادمة</h3>
                <div class="mt-4 grid gap-2">
                    @forelse ($upcomingServices as $schedule)
                        <div class="border-b border-gray-200 py-2 last:border-0 dark:border-gray-700">
                            <div class="font-medium">{{ $schedule->service?->name_ar ?? 'خدمة' }}</div>
                            <div class="text-sm text-gray-500">{{ $schedule->unit?->code ?? 'كامل العقار' }} · {{ $schedule->next_due_at?->format('Y-m-d H:i') }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد خدمات مجدولة قريبة.</p>
                    @endforelse
                </div>
            </div>

            <div class="fi-section p-5">
                <h3 class="text-lg font-bold">الصيانة المفتوحة</h3>
                <div class="mt-4 grid gap-2">
                    @forelse ($openMaintenance as $request)
                        <div class="border-b border-gray-200 py-2 last:border-0 dark:border-gray-700">
                            <div class="font-medium">{{ $request->title }}</div>
                            <div class="text-sm text-gray-500">{{ $request->unit?->code ?? 'كامل العقار' }} · {{ $request->status }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد طلبات صيانة مفتوحة.</p>
                    @endforelse
                </div>
            </div>

            <div class="fi-section p-5">
                <h3 class="text-lg font-bold">المستندات المنتهية قريبًا</h3>
                <div class="mt-4 grid gap-2">
                    @forelse ($expiringDocuments as $document)
                        <div class="border-b border-gray-200 py-2 last:border-0 dark:border-gray-700">
                            <div class="font-medium">{{ $document->title }}</div>
                            <div class="text-sm text-danger-600">{{ $document->expires_at?->format('Y-m-d') }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">لا توجد مستندات تنتهي خلال 60 يومًا.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="fi-section p-5">
            <h3 class="text-lg font-bold">آخر المصروفات</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="border-b text-start"><th class="p-2 text-start">التاريخ</th><th class="p-2 text-start">البيان</th><th class="p-2 text-start">المبلغ</th><th class="p-2 text-start">المورد</th></tr></thead>
                    <tbody>
                        @forelse ($recentExpenses as $expense)
                            <tr class="border-b last:border-0"><td class="p-2">{{ $expense->incurred_at?->format('Y-m-d') }}</td><td class="p-2">{{ $expense->description ?: $expense->category }}</td><td class="p-2">{{ number_format((float) $expense->amount, 2) }} {{ $expense->currency?->code }}</td><td class="p-2">{{ $expense->vendor?->name ?? '—' }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="p-3 text-gray-500">لا توجد مصروفات مسجلة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
