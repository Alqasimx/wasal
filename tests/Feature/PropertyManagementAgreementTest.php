<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyManagementAgreement;
use App\Models\PropertyType;
use App\Models\User;
use App\Services\PropertyManagementAgreementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PropertyManagementAgreementTest extends TestCase
{
    use RefreshDatabase;

    public function test_agreement_number_is_generated_automatically(): void
    {
        [$property, $user] = $this->context();

        $agreement = PropertyManagementAgreement::create([
            'property_id' => $property->id,
            'created_by_user_id' => $user->id,
            'starts_at' => '2026-10-01',
            'status' => PropertyManagementAgreement::STATUS_ACTIVE,
            'management_fee_type' => PropertyManagementAgreement::FEE_PERCENTAGE,
            'management_fee_value' => 5,
        ]);

        $this->assertNotNull($agreement->agreement_number);
        $this->assertStringStartsWith('PMA-', $agreement->agreement_number);
    }

    public function test_overlapping_active_management_agreements_are_blocked(): void
    {
        [$property, $user] = $this->context();

        PropertyManagementAgreement::create([
            'property_id' => $property->id,
            'created_by_user_id' => $user->id,
            'starts_at' => '2026-10-01',
            'ends_at' => '2026-12-31',
            'status' => PropertyManagementAgreement::STATUS_ACTIVE,
            'management_fee_type' => PropertyManagementAgreement::FEE_PERCENTAGE,
            'management_fee_value' => 5,
        ]);

        $this->expectException(ValidationException::class);

        app(PropertyManagementAgreementService::class)->assertNoOverlap(
            propertyId: $property->id,
            startsAt: '2026-11-01',
            endsAt: '2027-01-31',
        );
    }

    private function context(): array
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

        $type = PropertyType::create([
            'name_ar' => 'عمارة',
            'name_en' => 'Building',
            'slug' => 'building',
            'is_active' => true,
        ]);

        $property = Property::create([
            'created_by_user_id' => $user->id,
            'property_type_id' => $type->id,
            'internal_code' => 'PMA-TEST-1',
            'title_ar' => 'عقار اختبار إدارة',
            'status' => 'active',
            'city_id' => $city->id,
        ]);

        return [$property, $user];
    }
}
