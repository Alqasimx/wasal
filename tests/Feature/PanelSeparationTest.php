<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_wasal_panels_are_registered(): void
    {
        $this->assertSame('admin', Filament::getPanel('admin')->getId());
        $this->assertSame('real-estate', Filament::getPanel('real-estate')->getId());
        $this->assertSame('property-management', Filament::getPanel('property-management')->getId());
    }

    public function test_property_reviewer_accesses_real_estate_but_not_property_management(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('property_reviewer');

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('real-estate')));
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('property-management')));
    }

    public function test_property_management_role_accesses_property_management_but_not_real_estate(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('property_management');

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('property-management')));
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('real-estate')));
    }

    public function test_system_admin_can_access_all_panels(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('system_admin');

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('real-estate')));
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('property-management')));
    }
}
