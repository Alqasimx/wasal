<?php

namespace Tests\Feature;

use App\Models\PropertyDocument;
use App\Models\PropertyInspection;
use App\Models\Task;
use App\Models\UtilityMeter;
use App\Models\UtilityMeterReading;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedPropertyOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_populates_advanced_property_operations(): void
    {
        $this->seed(DemoDataSeeder::class);

        $this->assertSame(3, PropertyInspection::query()
            ->where('inspection_number', 'like', 'DEMO-INS-%')
            ->count());

        $this->assertSame(3, UtilityMeter::query()
            ->where('meter_number', 'like', 'DEMO-METER-%')
            ->count());

        $this->assertSame(6, UtilityMeterReading::query()
            ->whereHas('meter', fn ($query) =>
                $query->where('meter_number', 'like', 'DEMO-METER-%'))
            ->count());

        $this->assertSame(3, PropertyDocument::query()
            ->where('title', 'like', 'DEMO-DOC-%')
            ->count());
    }

    public function test_scheduled_inspection_creates_follow_up_task(): void
    {
        $this->seed(DemoDataSeeder::class);

        $inspection = PropertyInspection::query()
            ->where('inspection_number', 'DEMO-INS-001')
            ->firstOrFail();

        $this->assertDatabaseHas('tasks', [
            'related_type' => 'property_inspection',
            'related_id' => $inspection->id,
            'status' => Task::STATUS_PENDING,
        ]);
    }

    public function test_demo_meter_reading_calculates_consumption(): void
    {
        $this->seed(DemoDataSeeder::class);

        $meter = UtilityMeter::query()
            ->where('meter_number', 'DEMO-METER-E-001')
            ->firstOrFail();

        $reading = $meter->readings()
            ->latest('reading_at')
            ->firstOrFail();

        $this->assertSame(75.5, (float) $reading->consumption);
    }
}
