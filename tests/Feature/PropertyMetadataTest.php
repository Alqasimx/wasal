<?php

namespace Tests\Feature;

use App\Filament\Resources\PropertyFeatures\PropertyFeatureResource;
use App\Filament\Resources\PropertyTypes\PropertyTypeResource;
use App\Models\PropertyFeature;
use App\Models\PropertyType;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_active_property_types_are_returned_with_active_features(): void
    {
        $type = PropertyType::create([
            'name_ar' => 'شقة',
            'name_en' => 'Apartment',
            'slug' => 'apartment',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $feature = PropertyFeature::create([
            'name_ar' => 'عدد الغرف',
            'name_en' => 'Bedrooms',
            'slug' => 'bedrooms',
            'data_type' => 'integer',
            'is_filterable' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $inactiveFeature = PropertyFeature::create([
            'name_ar' => 'ميزة مخفية',
            'name_en' => 'Hidden feature',
            'slug' => 'hidden-feature',
            'data_type' => 'boolean',
            'is_active' => false,
        ]);

        $type->features()->attach([
            $feature->id => ['is_required' => true, 'sort_order' => 1],
            $inactiveFeature->id => ['is_required' => false, 'sort_order' => 2],
        ]);

        $this->getJson('/api/v1/property-types')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'apartment')
            ->assertJsonPath('data.0.features.0.slug', 'bedrooms')
            ->assertJsonPath('data.0.features.0.is_required', true)
            ->assertJsonMissing(['slug' => 'hidden-feature']);
    }

    public function test_inactive_property_types_are_not_publicly_returned(): void
    {
        PropertyType::create([
            'name_ar' => 'نوع غير نشط',
            'name_en' => 'Inactive type',
            'slug' => 'inactive-type',
            'is_active' => false,
        ]);

        $this->getJson('/api/v1/property-types')
            ->assertOk()
            ->assertJsonMissing(['slug' => 'inactive-type']);
    }

    public function test_features_can_be_filtered_by_property_type(): void
    {
        $type = PropertyType::create([
            'name_ar' => 'فيلا',
            'name_en' => 'Villa',
            'slug' => 'villa',
        ]);

        $feature = PropertyFeature::create([
            'name_ar' => 'حديقة',
            'name_en' => 'Garden',
            'slug' => 'garden',
            'data_type' => 'boolean',
            'is_active' => true,
        ]);

        $otherFeature = PropertyFeature::create([
            'name_ar' => 'مصعد',
            'name_en' => 'Elevator',
            'slug' => 'elevator',
            'data_type' => 'boolean',
            'is_active' => true,
        ]);

        $type->features()->attach($feature);

        $this->getJson('/api/v1/property-features?property_type_id='.$type->id)
            ->assertOk()
            ->assertJsonFragment(['slug' => 'garden'])
            ->assertJsonMissing(['slug' => 'elevator']);
    }

    public function test_property_metadata_permissions_are_assigned_to_property_reviewer(): void
    {
        $role = Role::findByName('property_reviewer', 'web');

        $this->assertTrue($role->hasPermissionTo('property_types.view'));
        $this->assertTrue($role->hasPermissionTo('property_types.manage'));
        $this->assertTrue($role->hasPermissionTo('property_features.view'));
        $this->assertTrue($role->hasPermissionTo('property_features.manage'));
    }

    public function test_filament_resources_require_property_metadata_permissions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertFalse(PropertyTypeResource::canViewAny());
        $this->assertFalse(PropertyFeatureResource::canViewAny());

        $user->assignRole('property_reviewer');
        $this->assertTrue(PropertyTypeResource::canViewAny());
        $this->assertTrue(PropertyFeatureResource::canViewAny());
    }
}
