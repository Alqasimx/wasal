<?php

namespace Tests\Feature;

use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\OwnerSettlement;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyInspection;
use App\Models\PropertyListing;
use App\Models\PropertyManagementAgreement;
use App\Models\PropertyRequest;
use App\Models\PropertyUnit;
use App\Models\PropertyVendor;
use App\Models\RentPayment;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\UtilityMeter;
use App\Models\UtilityMeterReading;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_populates_main_admin_sections(): void
    {
        $this->seed(DemoDataSeeder::class);

        $this->assertSame(3, Property::query()->where('internal_code', 'like', 'DEMO-PROP-%')->count());
        $this->assertSame(6, PropertyUnit::query()->whereHas('property', fn ($query) => $query->where('internal_code', 'like', 'DEMO-PROP-%'))->count());
        $this->assertSame(3, Tenant::query()->where('identity_number', 'like', 'DEMO-T-%')->count());
        $this->assertSame(3, Tenancy::query()->whereHas('unit.property', fn ($query) => $query->where('internal_code', 'like', 'DEMO-PROP-%'))->count());
        $this->assertSame(3, PropertyManagementAgreement::query()->where('agreement_number', 'like', 'DEMO-PMA-%')->count());
        $this->assertSame(3, PropertyListing::query()->where('listing_number', 'like', 'DEMO-LIST-%')->count());
        $this->assertSame(3, PropertyRequest::query()->where('reference_number', 'like', 'DEMO-REQ-%')->count());
        $this->assertSame(3, PropertyVendor::query()->where('email', 'like', '%@demo.local')->count());
        $this->assertSame(3, MaintenanceRequest::query()->where('reference_number', 'like', 'DEMO-MNT-%')->count());
        $this->assertSame(3, RentPayment::query()->where('receipt_number', 'like', 'DEMO-RCP-%')->count());
        $this->assertSame(3, Notification::query()->count());
        $this->assertSame(3, OwnerSettlement::query()->count());
        $this->assertSame(3, PropertyInspection::query()->where('inspection_number', 'like', 'DEMO-INS-%')->count());
        $this->assertSame(3, UtilityMeter::query()->where('meter_number', 'like', 'DEMO-METER-%')->count());
        $this->assertSame(6, UtilityMeterReading::query()->count());
        $this->assertSame(3, PropertyDocument::query()->where('title', 'like', 'DEMO-DOC-%')->count());
    }

    public function test_demo_seeder_can_be_run_twice_without_duplicate_demo_records(): void
    {
        $this->seed(DemoDataSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $this->assertSame(3, Property::query()->where('internal_code', 'like', 'DEMO-PROP-%')->count());
        $this->assertSame(3, Tenant::query()->where('identity_number', 'like', 'DEMO-T-%')->count());
        $this->assertSame(3, MaintenanceRequest::query()->where('reference_number', 'like', 'DEMO-MNT-%')->count());
        $this->assertSame(3, RentPayment::query()->where('receipt_number', 'like', 'DEMO-RCP-%')->count());
        $this->assertSame(3, Notification::query()->count());
        $this->assertSame(3, OwnerSettlement::query()->count());
    }
}
