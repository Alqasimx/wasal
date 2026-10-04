<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyManagementAgreement;
use App\Models\PropertyType;
use App\Models\PropertyUnit;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PropertyUnitOccupancyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-04 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_currently_managed_scope_returns_only_units_under_active_management(): void
    {
        [$managedProperty, $unmanagedProperty, $user] = $this->properties();

        PropertyManagementAgreement::create([
            'property_id' => $managedProperty->id,
            'created_by_user_id' => $user->id,
            'starts_at' => '2026-10-01',
            'status' => PropertyManagementAgreement::STATUS_ACTIVE,
            'management_fee_type' => PropertyManagementAgreement::FEE_PERCENTAGE,
            'management_fee_value' => 5,
        ]);

        $managedUnit = PropertyUnit::create([
            'property_id' => $managedProperty->id,
            'code' => 'M-01',
            'name' => 'وحدة مدارة',
            'status' => PropertyUnit::STATUS_AVAILABLE,
        ]);

        PropertyUnit::create([
            'property_id' => $unmanagedProperty->id,
            'code' => 'U-01',
            'name' => 'وحدة غير مدارة',
            'status' => PropertyUnit::STATUS_AVAILABLE,
        ]);

        $ids = PropertyUnit::query()
            ->currentlyManaged()
            ->pluck('id')
            ->all();

        $this->assertSame([$managedUnit->id], $ids);
    }

    public function test_occupancy_is_derived_from_current_active_tenancy(): void
    {
        [$managedProperty, , $user, $currency] = $this->properties();

        PropertyManagementAgreement::create([
            'property_id' => $managedProperty->id,
            'created_by_user_id' => $user->id,
            'starts_at' => '2026-10-01',
            'status' => PropertyManagementAgreement::STATUS_ACTIVE,
            'management_fee_type' => PropertyManagementAgreement::FEE_PERCENTAGE,
            'management_fee_value' => 5,
        ]);

        $unit = PropertyUnit::create([
            'property_id' => $managedProperty->id,
            'code' => 'M-01',
            'name' => 'الوحدة 1',
            'status' => PropertyUnit::STATUS_AVAILABLE,
        ]);

        $tenant = Tenant::create([
            'name' => 'مستأجر إشغال',
            'phone' => '770000001',
        ]);

        Tenancy::create([
            'property_unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'starts_at' => '2026-10-01',
            'ends_at' => '2026-12-31',
            'rent_amount' => 100000,
            'currency_id' => $currency->id,
            'payment_frequency' => Tenancy::FREQUENCY_MONTHLY,
            'status' => Tenancy::STATUS_ACTIVE,
        ]);

        $this->assertTrue($unit->fresh()->isOccupied());
        $this->assertSame(
            $tenant->id,
            $unit->fresh()->currentTenancy?->tenant_id
        );
    }

    public function test_future_tenancy_does_not_mark_unit_as_currently_occupied(): void
    {
        [$managedProperty, , $user, $currency] = $this->properties();

        PropertyManagementAgreement::create([
            'property_id' => $managedProperty->id,
            'created_by_user_id' => $user->id,
            'starts_at' => '2026-10-01',
            'status' => PropertyManagementAgreement::STATUS_ACTIVE,
            'management_fee_type' => PropertyManagementAgreement::FEE_PERCENTAGE,
            'management_fee_value' => 5,
        ]);

        $unit = PropertyUnit::create([
            'property_id' => $managedProperty->id,
            'code' => 'M-02',
            'name' => 'الوحدة 2',
            'status' => PropertyUnit::STATUS_AVAILABLE,
        ]);

        $tenant = Tenant::create([
            'name' => 'مستأجر مستقبلي',
            'phone' => '770000002',
        ]);

        Tenancy::create([
            'property_unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'starts_at' => '2026-11-01',
            'ends_at' => '2027-10-31',
            'rent_amount' => 100000,
            'currency_id' => $currency->id,
            'payment_frequency' => Tenancy::FREQUENCY_MONTHLY,
            'status' => Tenancy::STATUS_ACTIVE,
        ]);

        $this->assertFalse($unit->fresh()->isOccupied());
    }

    public function test_management_agreement_reports_occupancy_percentage(): void
    {
        [$managedProperty, , $user, $currency] = $this->properties();

        $agreement = PropertyManagementAgreement::create([
            'property_id' => $managedProperty->id,
            'created_by_user_id' => $user->id,
            'starts_at' => '2026-10-01',
            'status' => PropertyManagementAgreement::STATUS_ACTIVE,
            'management_fee_type' => PropertyManagementAgreement::FEE_PERCENTAGE,
            'management_fee_value' => 5,
        ]);

        $occupiedUnit = PropertyUnit::create([
            'property_id' => $managedProperty->id,
            'code' => 'M-01',
            'name' => 'الوحدة 1',
            'status' => PropertyUnit::STATUS_AVAILABLE,
        ]);

        PropertyUnit::create([
            'property_id' => $managedProperty->id,
            'code' => 'M-02',
            'name' => 'الوحدة 2',
            'status' => PropertyUnit::STATUS_AVAILABLE,
        ]);

        $tenant = Tenant::create([
            'name' => 'مستأجر النسبة',
            'phone' => '770000003',
        ]);

        Tenancy::create([
            'property_unit_id' => $occupiedUnit->id,
            'tenant_id' => $tenant->id,
            'starts_at' => '2026-10-01',
            'ends_at' => '2026-12-31',
            'rent_amount' => 100000,
            'currency_id' => $currency->id,
            'payment_frequency' => Tenancy::FREQUENCY_MONTHLY,
            'status' => Tenancy::STATUS_ACTIVE,
        ]);

        $this->assertSame(2, $agreement->unitsCount());
        $this->assertSame(1, $agreement->activeUnitsCount());
        $this->assertSame(50.0, $agreement->occupancyPercentage());
    }

    private function properties(): array
    {
        $user = User::factory()->create();

        $country = Country::create([
            'code' => 'YE',
            'name_ar' => 'اليمن',
            'name_en' => 'Yemen',
            'is_active' => true,
        ]);

        $governorate = Governorate::create([
            'country_id' => $country->id,
            'code' => 'AD',
            'name_ar' => 'عدن',
            'name_en' => 'Aden',
            'is_active' => true,
        ]);

        $city = City::create([
            'governorate_id' => $governorate->id,
            'code' => 'ADEN',
            'name_ar' => 'عدن',
            'name_en' => 'Aden',
            'is_active' => true,
        ]);

        $currency = Currency::create([
            'code' => 'YER',
            'iso_code' => 'YER',
            'name_ar' => 'ريال يمني',
            'name_en' => 'Yemeni Rial',
            'is_active' => true,
        ]);

        $type = PropertyType::create([
            'name_ar' => 'عمارة',
            'name_en' => 'Building',
            'slug' => 'occupancy-building',
            'is_active' => true,
        ]);

        $managedProperty = Property::create([
            'created_by_user_id' => $user->id,
            'property_type_id' => $type->id,
            'internal_code' => 'MGD-001',
            'title_ar' => 'عقار مدار',
            'status' => 'active',
            'city_id' => $city->id,
        ]);

        $unmanagedProperty = Property::create([
            'created_by_user_id' => $user->id,
            'property_type_id' => $type->id,
            'internal_code' => 'UNM-001',
            'title_ar' => 'عقار غير مدار',
            'status' => 'active',
            'city_id' => $city->id,
        ]);

        return [$managedProperty, $unmanagedProperty, $user, $currency];
    }
}
