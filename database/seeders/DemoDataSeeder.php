<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\City;
use App\Models\Currency;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyExpense;
use App\Models\PropertyListing;
use App\Models\PropertyManagementAgreement;
use App\Models\PropertyOwner;
use App\Models\PropertyRequest;
use App\Models\PropertyService;
use App\Models\PropertyServiceSchedule;
use App\Models\PropertyType;
use App\Models\PropertyUnit;
use App\Models\PropertyVendor;
use App\Models\RentDueItem;
use App\Models\RentPayment;
use App\Models\Task;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            GeographySeeder::class,
            CurrencySeeder::class,
            PropertyMetadataSeeder::class,
            PropertyManagementServiceCatalogSeeder::class,
        ]);

        $currency = Currency::query()->where('code', 'YER_NEW')->firstOrFail();
        $city = City::query()->where('code', 'ADEN-CITY')->firstOrFail();

        $staff = User::withTrashed()->updateOrCreate(
            ['email' => 'demo.manager@wasal.local'],
            [
                'name' => 'مدير أملاك تجريبي',
                'phone' => '700000001',
                'whatsapp_phone' => '700000001',
                'password' => Hash::make('password'),
                'preferred_language' => 'ar',
                'preferred_currency_id' => $currency->id,
                'preferred_city_id' => $city->id,
                'status' => 'active',
                'deleted_at' => null,
            ],
        );
        $staff->syncRoles(['property_management']);

        $customers = collect([
            ['name' => 'أحمد سالم', 'email' => 'demo.customer1@wasal.local', 'phone' => '700000011'],
            ['name' => 'محمد علي', 'email' => 'demo.customer2@wasal.local', 'phone' => '700000012'],
            ['name' => 'سارة عبدالله', 'email' => 'demo.customer3@wasal.local', 'phone' => '700000013'],
        ])->map(function (array $data) use ($currency, $city): User {
            $user = User::withTrashed()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'whatsapp_phone' => $data['phone'],
                    'password' => Hash::make('password'),
                    'preferred_language' => 'ar',
                    'preferred_currency_id' => $currency->id,
                    'preferred_city_id' => $city->id,
                    'status' => 'active',
                    'deleted_at' => null,
                ],
            );

            $user->syncRoles(['user']);

            return $user;
        });

        $bank = Bank::updateOrCreate(
            ['account_number' => 'DEMO-001'],
            [
                'name' => 'حساب وصال التجريبي',
                'account_name' => 'Wasal Demo',
                'iban' => null,
                'currency_id' => $currency->id,
                'instructions' => 'حساب تجريبي لاختبار التحصيلات فقط.',
                'is_active' => true,
                'sort_order' => 100,
            ],
        );

        $propertyTypes = [
            'building' => PropertyType::query()->where('slug', 'building')->firstOrFail(),
            'villa' => PropertyType::query()->where('slug', 'villa')->firstOrFail(),
            'apartment' => PropertyType::query()->where('slug', 'apartment')->firstOrFail(),
        ];

        $propertiesData = [
            [
                'code' => 'DEMO-PROP-001',
                'type' => 'building',
                'title' => 'عمارة وصال السكنية',
                'description' => 'عمارة سكنية تجريبية متعددة الوحدات لاختبار إدارة الأملاك والإشغال والتحصيل.',
                'area' => 640,
                'floors' => 4,
                'units' => 4,
                'year' => 2021,
                'owner' => 'عبدالرحمن أحمد',
                'street' => 'شارع التسعين',
            ],
            [
                'code' => 'DEMO-PROP-002',
                'type' => 'villa',
                'title' => 'فيلا النخيل',
                'description' => 'فيلا تجريبية لإظهار خدمات الصيانة الدورية وإدارة عقد إيجار مستقل.',
                'area' => 520,
                'floors' => 2,
                'units' => 1,
                'year' => 2020,
                'owner' => 'خالد محمد',
                'street' => 'شارع المطار',
            ],
            [
                'code' => 'DEMO-PROP-003',
                'type' => 'building',
                'title' => 'مبنى الأعمال',
                'description' => 'مبنى تجريبي يضم وحدات مكتبية وتجارية لإظهار حالات الإشغال المختلفة.',
                'area' => 780,
                'floors' => 3,
                'units' => 3,
                'year' => 2022,
                'owner' => 'مؤسسة المستقبل',
                'street' => 'شارع المعلا الرئيسي',
            ],
        ];

        $properties = collect();

        foreach ($propertiesData as $index => $data) {
            $property = Property::withTrashed()->updateOrCreate(
                ['internal_code' => $data['code']],
                [
                    'created_by_user_id' => $staff->id,
                    'property_type_id' => $propertyTypes[$data['type']]->id,
                    'title_ar' => $data['title'],
                    'title_en' => null,
                    'description_ar' => $data['description'],
                    'status' => 'active',
                    'city_id' => $city->id,
                    'street_name' => $data['street'],
                    'public_location_text' => 'عدن — موقع تجريبي',
                    'exact_address' => 'عنوان تجريبي خاص بلوحة التحكم',
                    'area' => $data['area'],
                    'floors_count' => $data['floors'],
                    'units_count' => $data['units'],
                    'year_built' => $data['year'],
                    'gallery' => [],
                    'deleted_at' => null,
                ],
            );

            $owner = PropertyOwner::updateOrCreate(
                [
                    'property_id' => $property->id,
                    'external_owner_name' => $data['owner'],
                ],
                [
                    'ownership_percentage' => 100,
                    'is_primary' => true,
                    'valid_from' => today()->subYear(),
                    'valid_to' => null,
                ],
            );

            PropertyManagementAgreement::withTrashed()->updateOrCreate(
                ['agreement_number' => 'DEMO-PMA-00'.($index + 1)],
                [
                    'property_id' => $property->id,
                    'property_owner_id' => $owner->id,
                    'assigned_manager_user_id' => $staff->id,
                    'created_by_user_id' => $staff->id,
                    'starts_at' => today()->subMonths(6),
                    'ends_at' => today()->addYear(),
                    'status' => PropertyManagementAgreement::STATUS_ACTIVE,
                    'management_fee_type' => PropertyManagementAgreement::FEE_PERCENTAGE,
                    'management_fee_value' => 7.5,
                    'fee_billing_frequency' => PropertyManagementAgreement::BILLING_MONTHLY,
                    'auto_renew' => true,
                    'renewal_notice_days' => 30,
                    'currency_id' => $currency->id,
                    'included_services' => [],
                    'notes' => 'اتفاق إدارة تجريبي.',
                    'deleted_at' => null,
                ],
            );

            PropertyListing::withTrashed()->updateOrCreate(
                ['listing_number' => 'DEMO-LIST-00'.($index + 1)],
                [
                    'property_id' => $property->id,
                    'purpose' => 'rent',
                    'price' => [250000, 450000, 300000][$index],
                    'currency_id' => $currency->id,
                    'price_period' => 'monthly',
                    'public_title' => $data['title'].' للإيجار',
                    'public_description' => 'إعلان تجريبي من لوحة إدارة العقارات.',
                    'status' => PropertyListing::STATUS_PUBLISHED,
                    'published_at' => now()->subDays(10 - $index),
                    'expires_at' => now()->addMonths(3),
                    'created_by_user_id' => $staff->id,
                    'reviewed_by_user_id' => $staff->id,
                    'reviewed_at' => now()->subDays(10 - $index),
                    'share_enabled' => true,
                    'deleted_at' => null,
                ],
            );

            $properties->push($property);
        }

        $unitDefinitions = [
            ['DEMO-PROP-001', 'A-101', 'شقة 101', 1, 'شقة', 125, 3, 2, 1, PropertyUnit::STATUS_AVAILABLE],
            ['DEMO-PROP-001', 'A-102', 'شقة 102', 1, 'شقة', 118, 3, 2, 1, PropertyUnit::STATUS_AVAILABLE],
            ['DEMO-PROP-001', 'A-201', 'شقة 201', 2, 'شقة', 130, 3, 2, 1, PropertyUnit::STATUS_MAINTENANCE],
            ['DEMO-PROP-002', 'V-01', 'الفيلا كاملة', 0, 'فيلا', 520, 5, 4, 2, PropertyUnit::STATUS_AVAILABLE],
            ['DEMO-PROP-003', 'O-101', 'مكتب 101', 1, 'مكتب', 95, 0, 1, 1, PropertyUnit::STATUS_AVAILABLE],
            ['DEMO-PROP-003', 'O-102', 'مكتب 102', 1, 'مكتب', 105, 0, 1, 1, PropertyUnit::STATUS_AVAILABLE],
        ];

        $units = collect();

        foreach ($unitDefinitions as $definition) {
            [$propertyCode, $code, $name, $floor, $type, $area, $bedrooms, $bathrooms, $halls, $status] = $definition;
            $property = $properties->firstWhere('internal_code', $propertyCode);

            $units->put(
                $code,
                PropertyUnit::withTrashed()->updateOrCreate(
                    ['property_id' => $property->id, 'code' => $code],
                    [
                        'name' => $name,
                        'floor_number' => $floor,
                        'unit_type' => $type,
                        'area' => $area,
                        'bedrooms' => $bedrooms,
                        'bathrooms' => $bathrooms,
                        'halls' => $halls,
                        'status' => $status,
                        'deleted_at' => null,
                    ],
                )
            );
        }

        $tenantsData = [
            ['name' => 'محمود صالح', 'phone' => '711111111', 'email' => 'tenant1@demo.local', 'identity' => 'DEMO-T-001'],
            ['name' => 'فاطمة أحمد', 'phone' => '722222222', 'email' => 'tenant2@demo.local', 'identity' => 'DEMO-T-002'],
            ['name' => 'شركة الأفق', 'phone' => '733333333', 'email' => 'tenant3@demo.local', 'identity' => 'DEMO-T-003'],
        ];

        $tenants = collect();

        foreach ($tenantsData as $data) {
            $tenants->push(Tenant::updateOrCreate(
                ['identity_number' => $data['identity']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'notes' => 'بيانات مستأجر تجريبية.',
                ],
            ));
        }

        $tenancyDefinitions = [
            [$units['A-101'], $tenants[0], 'DEMO-TEN-1', 180000, today()->subMonths(3), today()->addMonths(9)],
            [$units['V-01'], $tenants[1], 'DEMO-TEN-2', 350000, today()->subMonths(2), today()->addMonths(10)],
            [$units['O-101'], $tenants[2], 'DEMO-TEN-3', 220000, today()->subMonth(), today()->addMonths(11)],
        ];

        $tenancies = collect();

        foreach ($tenancyDefinitions as $i => [$unit, $tenant, $key, $rent, $start, $end]) {
            $tenancy = Tenancy::updateOrCreate(
                [
                    'property_unit_id' => $unit->id,
                    'tenant_id' => $tenant->id,
                    'starts_at' => $start->toDateString(),
                ],
                [
                    'contract_number' => $key,
                    'ends_at' => $end->toDateString(),
                    'rent_amount' => $rent,
                    'currency_id' => $currency->id,
                    'payment_frequency' => Tenancy::FREQUENCY_MONTHLY,
                    'due_day' => 1,
                    'grace_days' => 5,
                    'security_deposit' => (int) ($rent * 0.5),
                    'auto_generate_dues' => true,
                    'notes' => 'عقد إيجار تجريبي لإظهار دورة التحصيل.',
                    'status' => Tenancy::STATUS_ACTIVE,
                ],
            );

            $unit->update(['status' => PropertyUnit::STATUS_OCCUPIED]);
            $tenancies->push($tenancy);

            $pastDue = RentDueItem::updateOrCreate(
                [
                    'tenancy_id' => $tenancy->id,
                    'due_date' => today()->subMonth()->startOfMonth()->toDateString(),
                ],
                [
                    'amount' => $rent,
                    'currency_id' => $currency->id,
                    'status' => $i === 0 ? RentDueItem::STATUS_PAID : RentDueItem::STATUS_PARTIAL,
                    'paid_amount' => $i === 0 ? $rent : (int) ($rent * 0.5),
                ],
            );

            RentDueItem::updateOrCreate(
                [
                    'tenancy_id' => $tenancy->id,
                    'due_date' => today()->startOfMonth()->toDateString(),
                ],
                [
                    'amount' => $rent,
                    'currency_id' => $currency->id,
                    'status' => RentDueItem::STATUS_DUE,
                    'paid_amount' => 0,
                ],
            );

            RentPayment::updateOrCreate(
                ['receipt_number' => 'DEMO-RCP-00'.($i + 1)],
                [
                    'rent_due_item_id' => $pastDue->id,
                    'tenancy_id' => $tenancy->id,
                    'amount' => $i === 0 ? $rent : (int) ($rent * 0.5),
                    'currency_id' => $currency->id,
                    'paid_at' => now()->subDays(12 - $i),
                    'payment_method' => $i === 1 ? 'bank_transfer' : 'cash',
                    'bank_id' => $i === 1 ? $bank->id : null,
                    'reference_number' => 'DEMO-PAY-00'.($i + 1),
                    'notes' => 'دفعة إيجار تجريبية.',
                    'status' => RentPayment::STATUS_POSTED,
                    'recorded_by_user_id' => $staff->id,
                ],
            );
        }

        $vendors = collect([
            PropertyVendor::withTrashed()->updateOrCreate(
                ['phone' => '740000001'],
                [
                    'name' => 'مؤسسة النور للكهرباء',
                    'email' => 'electric@demo.local',
                    'service_categories' => ['electrical', 'solar'],
                    'notes' => 'مورد تجريبي.',
                    'is_active' => true,
                    'deleted_at' => null,
                ],
            ),
            PropertyVendor::withTrashed()->updateOrCreate(
                ['phone' => '740000002'],
                [
                    'name' => 'مؤسسة المياه للسباكة',
                    'email' => 'plumbing@demo.local',
                    'service_categories' => ['plumbing', 'pumps'],
                    'notes' => 'مورد تجريبي.',
                    'is_active' => true,
                    'deleted_at' => null,
                ],
            ),
            PropertyVendor::withTrashed()->updateOrCreate(
                ['phone' => '740000003'],
                [
                    'name' => 'النظافة الحديثة',
                    'email' => 'cleaning@demo.local',
                    'service_categories' => ['cleaning', 'solar'],
                    'notes' => 'مورد تجريبي.',
                    'is_active' => true,
                    'deleted_at' => null,
                ],
            ),
        ]);

        $services = [
            'ELEC' => PropertyService::query()->where('code', 'ELEC')->firstOrFail(),
            'PLUMB' => PropertyService::query()->where('code', 'PLUMB')->firstOrFail(),
            'SOLAR-CLEAN' => PropertyService::query()->where('code', 'SOLAR-CLEAN')->firstOrFail(),
        ];

        $scheduleDefinitions = [
            [$services['ELEC'], $properties[0], $units['A-101'], $vendors[0], PropertyService::FREQUENCY_QUARTERLY, 25000],
            [$services['PLUMB'], $properties[1], $units['V-01'], $vendors[1], PropertyService::FREQUENCY_SEMIANNUAL, 30000],
            [$services['SOLAR-CLEAN'], $properties[2], null, $vendors[2], PropertyService::FREQUENCY_MONTHLY, 20000],
        ];

        $schedules = collect();

        foreach ($scheduleDefinitions as $i => [$service, $property, $unit, $vendor, $frequency, $cost]) {
            $schedules->push(PropertyServiceSchedule::updateOrCreate(
                [
                    'property_service_id' => $service->id,
                    'property_id' => $property->id,
                    'property_unit_id' => $unit?->id,
                ],
                [
                    'property_vendor_id' => $vendor->id,
                    'assigned_to_user_id' => $staff->id,
                    'frequency' => $frequency,
                    'interval_count' => 1,
                    'starts_at' => today()->subMonth(),
                    'next_due_at' => now()->addDays(3 + ($i * 4)),
                    'notify_before_minutes' => 1440,
                    'estimated_cost' => $cost,
                    'currency_id' => $currency->id,
                    'is_active' => true,
                    'notes' => 'جدول خدمة تجريبي.',
                ],
            ));
        }

        $maintenanceData = [
            [
                'ref' => 'DEMO-MNT-001',
                'property' => $properties[0],
                'unit' => $units['A-101'],
                'tenant' => $tenants[0],
                'service' => $services['ELEC'],
                'schedule' => $schedules[0],
                'vendor' => $vendors[0],
                'title' => 'فحص لوحة الكهرباء',
                'description' => 'فحص قاطع رئيسي وارتفاع حرارة في لوحة التوزيع.',
                'priority' => MaintenanceRequest::PRIORITY_HIGH,
                'status' => MaintenanceRequest::STATUS_SCHEDULED,
                'cost' => 25000,
                'days' => 2,
            ],
            [
                'ref' => 'DEMO-MNT-002',
                'property' => $properties[1],
                'unit' => $units['V-01'],
                'tenant' => $tenants[1],
                'service' => $services['PLUMB'],
                'schedule' => $schedules[1],
                'vendor' => $vendors[1],
                'title' => 'إصلاح تسرب مياه',
                'description' => 'متابعة تسرب أسفل حوض المطبخ واستبدال الوصلة.',
                'priority' => MaintenanceRequest::PRIORITY_URGENT,
                'status' => MaintenanceRequest::STATUS_IN_PROGRESS,
                'cost' => 18000,
                'days' => 1,
            ],
            [
                'ref' => 'DEMO-MNT-003',
                'property' => $properties[2],
                'unit' => null,
                'tenant' => null,
                'service' => $services['SOLAR-CLEAN'],
                'schedule' => $schedules[2],
                'vendor' => $vendors[2],
                'title' => 'تنظيف الألواح الشمسية',
                'description' => 'تنظيف دوري للألواح وفحص بصري للتوصيلات.',
                'priority' => MaintenanceRequest::PRIORITY_NORMAL,
                'status' => MaintenanceRequest::STATUS_OPEN,
                'cost' => 20000,
                'days' => 5,
            ],
        ];

        $maintenanceRequests = collect();

        foreach ($maintenanceData as $data) {
            $maintenanceRequests->push(MaintenanceRequest::updateOrCreate(
                ['reference_number' => $data['ref']],
                [
                    'property_id' => $data['property']->id,
                    'property_unit_id' => $data['unit']?->id,
                    'tenant_id' => $data['tenant']?->id,
                    'property_service_id' => $data['service']->id,
                    'property_service_schedule_id' => $data['schedule']->id,
                    'property_vendor_id' => $data['vendor']->id,
                    'assigned_to_user_id' => $staff->id,
                    'created_by_user_id' => $staff->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'priority' => $data['priority'],
                    'status' => $data['status'],
                    'scheduled_at' => now()->addDays($data['days']),
                    'started_at' => $data['status'] === MaintenanceRequest::STATUS_IN_PROGRESS ? now()->subHours(4) : null,
                    'estimated_cost' => $data['cost'],
                    'actual_cost' => $data['status'] === MaintenanceRequest::STATUS_IN_PROGRESS ? $data['cost'] : null,
                    'currency_id' => $currency->id,
                    'cost_bearer' => MaintenanceRequest::COST_OWNER,
                    'is_paid' => false,
                    'attachments' => [],
                    'notes' => 'طلب صيانة تجريبي.',
                ],
            ));
        }

        foreach ($maintenanceRequests as $i => $request) {
            PropertyExpense::updateOrCreate(
                ['reference_number' => 'DEMO-EXP-00'.($i + 1)],
                [
                    'property_id' => $request->property_id,
                    'property_unit_id' => $request->property_unit_id,
                    'property_management_agreement_id' => PropertyManagementAgreement::query()
                        ->where('property_id', $request->property_id)
                        ->value('id'),
                    'maintenance_request_id' => $request->id,
                    'property_vendor_id' => $request->property_vendor_id,
                    'category' => $i === 2 ? 'cleaning' : 'maintenance',
                    'description' => $request->title,
                    'amount' => $request->estimated_cost,
                    'paid_amount' => $i === 0 ? $request->estimated_cost : 0,
                    'currency_id' => $currency->id,
                    'incurred_at' => today()->subDays($i + 1),
                    'cost_bearer' => 'owner',
                    'payment_status' => $i === 0 ? PropertyExpense::STATUS_PAID : PropertyExpense::STATUS_UNPAID,
                    'notes' => 'مصروف تجريبي مرتبط بالصيانة.',
                    'created_by_user_id' => $staff->id,
                ],
            );

            Task::updateOrCreate(
                [
                    'related_type' => 'maintenance_request',
                    'related_id' => $request->id,
                ],
                [
                    'title' => 'متابعة: '.$request->title,
                    'description' => 'مهمة تجريبية مرتبطة بطلب الصيانة '.$request->reference_number,
                    'assigned_to_user_id' => $staff->id,
                    'created_by_user_id' => $staff->id,
                    'due_at' => $request->scheduled_at,
                    'recurrence' => Task::RECURRENCE_ONCE,
                    'status' => $request->status === MaintenanceRequest::STATUS_IN_PROGRESS
                        ? Task::STATUS_IN_PROGRESS
                        : Task::STATUS_PENDING,
                    'notify_before_minutes' => 1440,
                    'notified_at' => null,
                ],
            );
        }

        $notificationRecipients = User::role(['system_admin', 'property_management'])
            ->where('status', 'active')
            ->get();

        foreach ($notificationRecipients as $recipient) {
            Notification::updateOrCreate(
                [
                    'user_id' => $recipient->id,
                    'type' => 'maintenance_due',
                    'title' => 'صيانة كهرباء قريبة',
                ],
                [
                    'body' => 'موعد فحص لوحة الكهرباء في عمارة وصال السكنية قريب.',
                    'data' => [
                        'maintenance_request_id' => $maintenanceRequests[0]->id,
                        'task_id' => Task::query()
                            ->where('related_type', 'maintenance_request')
                            ->where('related_id', $maintenanceRequests[0]->id)
                            ->value('id'),
                    ],
                    'read_at' => null,
                ],
            );

            Notification::updateOrCreate(
                [
                    'user_id' => $recipient->id,
                    'type' => 'rent_due',
                    'title' => 'استحقاق إيجار يحتاج متابعة',
                ],
                [
                    'body' => 'يوجد استحقاق إيجار حالي غير مسدد بالكامل لأحد العقود التجريبية.',
                    'data' => [
                        'rent_due_item_id' => RentDueItem::query()
                            ->where('tenancy_id', $tenancies[1]->id)
                            ->latest('due_date')
                            ->value('id'),
                    ],
                    'read_at' => null,
                ],
            );

            Notification::updateOrCreate(
                [
                    'user_id' => $recipient->id,
                    'type' => 'maintenance_due',
                    'title' => 'تنظيف الألواح الشمسية مجدول',
                ],
                [
                    'body' => 'تنظيف الألواح الشمسية في مبنى الأعمال مجدول خلال الأيام القادمة.',
                    'data' => [
                        'maintenance_request_id' => $maintenanceRequests[2]->id,
                        'task_id' => Task::query()
                            ->where('related_type', 'maintenance_request')
                            ->where('related_id', $maintenanceRequests[2]->id)
                            ->value('id'),
                    ],
                    'read_at' => null,
                ],
            );
        }

        foreach ($customers as $i => $customer) {
            PropertyRequest::updateOrCreate(
                ['reference_number' => 'DEMO-REQ-00'.($i + 1)],
                [
                    'user_id' => $customer->id,
                    'source' => 'admin',
                    'purpose' => $i === 2 ? 'buy' : 'rent',
                    'property_type_id' => [$propertyTypes['apartment']->id, $propertyTypes['villa']->id, $propertyTypes['building']->id][$i],
                    'city_id' => $city->id,
                    'min_price' => [150000, 300000, 50000000][$i],
                    'max_price' => [250000, 500000, 90000000][$i],
                    'currency_id' => $currency->id,
                    'requirements_json' => [
                        'demo' => true,
                        'note' => 'طلب عقار تجريبي لعرض الواجهة.',
                    ],
                    'wants_field_search' => $i === 1,
                    'assigned_to_user_id' => $staff->id,
                    'status' => [
                        PropertyRequest::STATUS_NEW,
                        PropertyRequest::STATUS_SEARCHING,
                        PropertyRequest::STATUS_MATCHED,
                    ][$i],
                    'notes' => 'طلب تجريبي.',
                ],
            );
        }

        $this->command?->info('تم إنشاء بيانات وصال التجريبية بنجاح.');
        $this->command?->info('مدير إدارة الأملاك التجريبي: demo.manager@wasal.local / password');
    }
}
