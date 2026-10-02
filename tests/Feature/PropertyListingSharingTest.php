<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyAttributeValue;
use App\Models\PropertyFeature;
use App\Models\PropertyListing;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyListingSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_listing_exposes_approximate_coordinates_but_not_exact_coordinates(): void
    {
        $listing = $this->publishedListing();

        $this->getJson('/api/v1/property-listings/'.$listing->id)
            ->assertOk()
            ->assertJsonPath('data.property.public_latitude', '15.3694450')
            ->assertJsonPath('data.property.public_longitude', '44.1910060')
            ->assertJsonMissingPath('data.property.exact_latitude')
            ->assertJsonMissingPath('data.property.exact_longitude');
    }

    public function test_shared_offer_returns_only_published_listing_and_counts_share(): void
    {
        $listing = $this->publishedListing();

        $this->getJson('/api/v1/shared-offers/'.$listing->share_token)
            ->assertOk()
            ->assertJsonPath('data.listing_number', $listing->listing_number)
            ->assertJsonPath('data.share_url', url('/api/v1/shared-offers/'.$listing->share_token));

        $this->assertSame(1, $listing->fresh()->share_count);
    }

    public function test_draft_listing_is_not_publicly_accessible(): void
    {
        $listing = $this->publishedListing();
        $listing->update(['status' => PropertyListing::STATUS_DRAFT]);

        $this->getJson('/api/v1/property-listings/'.$listing->id)->assertNotFound();
        $this->getJson('/api/v1/shared-offers/'.$listing->share_token)->assertNotFound();
    }

    public function test_public_listings_can_be_filtered_by_dynamic_attribute(): void
    {
        $listing = $this->publishedListing();
        $feature = PropertyFeature::create([
            'name_ar' => 'غرف النوم',
            'name_en' => 'Bedrooms',
            'slug' => 'bedrooms',
            'key' => 'bedrooms',
            'data_type' => 'integer',
            'is_filterable' => true,
            'is_active' => true,
        ]);

        PropertyAttributeValue::create([
            'attributable_type' => Property::class,
            'attributable_id' => $listing->property_id,
            'property_feature_id' => $feature->id,
            'value_number' => 3,
        ]);

        $this->getJson('/api/v1/property-listings?attribute[bedrooms]=3')
            ->assertOk()
            ->assertJsonPath('data.0.listing_number', $listing->listing_number)
            ->assertJsonPath('data.0.property.attributes.0.key', 'bedrooms');
    }

    private function publishedListing(): PropertyListing
    {
        $user = User::factory()->create();
        $country = Country::create(['code' => 'YE', 'name_ar' => 'اليمن', 'name_en' => 'Yemen', 'is_active' => true]);
        $governorate = Governorate::create(['country_id' => $country->id, 'code' => 'SA', 'name_ar' => 'صنعاء', 'name_en' => 'Sana’a', 'is_active' => true]);
        $city = City::create(['governorate_id' => $governorate->id, 'code' => 'SAN', 'name_ar' => 'صنعاء', 'name_en' => 'Sana’a', 'is_active' => true]);
        $currency = Currency::create(['code' => 'YER', 'iso_code' => 'YER', 'name_ar' => 'ريال يمني', 'name_en' => 'Yemeni Rial', 'is_active' => true]);
        $type = PropertyType::create(['name_ar' => 'شقة', 'name_en' => 'Apartment', 'slug' => 'apartment', 'is_active' => true]);
        $property = Property::create([
            'created_by_user_id' => $user->id,
            'property_type_id' => $type->id,
            'internal_code' => 'PROP-001',
            'title_ar' => 'شقة اختبار',
            'status' => 'active',
            'city_id' => $city->id,
            'public_location_text' => 'بالقرب من وسط المدينة',
            'public_latitude' => 15.369445,
            'public_longitude' => 44.191006,
            'exact_latitude' => 15.370001,
            'exact_longitude' => 44.192002,
        ]);

        return PropertyListing::create([
            'property_id' => $property->id,
            'listing_number' => 'LIST-001',
            'purpose' => 'rent',
            'price' => 50000,
            'currency_id' => $currency->id,
            'price_period' => 'monthly',
            'public_title' => 'شقة للإيجار',
            'status' => PropertyListing::STATUS_PUBLISHED,
            'published_at' => now(),
            'created_by_user_id' => $user->id,
        ]);
    }
}
