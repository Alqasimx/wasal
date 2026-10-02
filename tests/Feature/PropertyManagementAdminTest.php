<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PropertyUnit;
use App\Models\RentDueItem;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenancyService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PropertyManagementAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_management_role_has_tenant_contract_and_due_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $role = Role::findByName('property_management', 'web');

        $this->assertTrue($role->hasPermissionTo('tenants.view'));
        $this->assertTrue($role->hasPermissionTo('tenants.manage'));
        $this->assertTrue($role->hasPermissionTo('tenancies.view'));
        $this->assertTrue($role->hasPermissionTo('tenancies.manage'));
        $this->assertTrue($role->hasPermissionTo('rent_due_items.view'));
        $this->assertTrue($role->hasPermissionTo('rent_due_items.manage'));
    }

    public function test_accountant_can_manage_rent_dues_without_managing_tenants_or_contracts(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $role = Role::findByName('accountant', 'web');

        $this->assertTrue($role->hasPermissionTo('tenants.view'));
        $this->assertFalse($role->hasPermissionTo('tenants.manage'));
        $this->assertTrue($role->hasPermissionTo('tenancies.view'));
        $this->assertFalse($role->hasPermissionTo('tenancies.manage'));
        $this->assertTrue($role->hasPermissionTo('rent_due_items.view'));
        $this->assertTrue($role->hasPermissionTo('rent_due_items.manage'));
    }

    public function test_active_tenancies_cannot_overlap_on_the_same_unit(): void
    {
        [$unit, $tenant, $currency] = $this->propertyManagementContext();

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

        $this->expectException(ValidationException::class);

        app(TenancyService::class)->assertUnitIsAvailable(
            propertyUnitId: $unit->id,
            startsAt: '2026-11-01',
            endsAt: '2027-01-31',
        );
    }

    public function test_non_overlapping_tenancy_period_is_allowed(): void
    {
        [$unit, $tenant, $currency] = $this->propertyManagementContext();

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

        app(TenancyService::class)->assertUnitIsAvailable(
            propertyUnitId: $unit->id,
            startsAt: '2027-01-01',
            endsAt: '2027-12-31',
        );

        $this->assertTrue(true);
    }

    public function test_partially_paid_rent_due_item_is_still_due(): void
    {
        [$unit, $tenant, $currency] = $this->propertyManagementContext();

        $tenancy = Tenancy::create([
            'property_unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'starts_at' => '2026-10-01',
            'rent_amount' => 100000,
            'currency_id' => $currency->id,
            'payment_frequency' => Tenancy::FREQUENCY_MONTHLY,
            'status' => Tenancy::STATUS_ACTIVE,
        ]);

        $dueItem = RentDueItem::create([
            'tenancy_id' => $tenancy->id,
            'due_date' => '2026-11-01',
            'amount' => 100000,
            'currency_id' => $currency->id,
            'status' => RentDueItem::STATUS_PARTIAL,
            'paid_amount' => 25000,
        ]);

        $this->assertTrue($dueItem->isDue());
    }

    private function propertyManagementContext(): array
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
            'name_ar' => 'شقة',
            'name_en' => 'Apartment',
            'slug' => 'apartment',
            'is_active' => true,
        ]);

        $property = Property::create([
            'created_by_user_id' => $user->id,
            'property_type_id' => $type->id,
            'internal_code' => 'PM-001',
            'title_ar' => 'عقار إدارة أملاك',
            'status' => 'active',
            'city_id' => $city->id,
        ]);

        $unit = PropertyUnit::create([
            'property_id' => $property->id,
            'code' => 'U-01',
            'name' => 'الوحدة 1',
            'status' => 'available',
        ]);

        $tenant = Tenant::create([
            'name' => 'مستأجر اختبار',
            'phone' => '770000000',
        ]);

        return [$unit, $tenant, $currency];
    }
}
