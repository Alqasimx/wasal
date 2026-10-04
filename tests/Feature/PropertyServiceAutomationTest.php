<?php

namespace Tests\Feature;

use App\Models\MaintenanceRequest;
use App\Models\PropertyServiceSchedule;
use App\Models\Task;
use App\Services\PropertyServiceScheduleService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyServiceAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_service_schedule_creates_work_order_and_task(): void
    {
        $this->seed(DemoDataSeeder::class);

        $schedule = PropertyServiceSchedule::query()
            ->where('is_active', true)
            ->firstOrFail();

        $schedule->update([
            'next_due_at' => now()->subMinute(),
            'last_generated_due_at' => null,
        ]);

        $beforeRequests = MaintenanceRequest::query()->count();
        $beforeTasks = Task::query()->count();

        $created = app(PropertyServiceScheduleService::class)
            ->generateDueTasks(now());

        $this->assertSame(1, $created);
        $this->assertSame($beforeRequests + 1, MaintenanceRequest::query()->count());
        $this->assertSame($beforeTasks + 1, Task::query()->count());

        $request = MaintenanceRequest::query()
            ->where('property_service_schedule_id', $schedule->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(MaintenanceRequest::STATUS_SCHEDULED, $request->status);
        $this->assertSame($schedule->property_id, $request->property_id);
        $this->assertSame($schedule->property_service_id, $request->property_service_id);

        $this->assertDatabaseHas('tasks', [
            'related_type' => 'maintenance_request',
            'related_id' => $request->id,
        ]);

        $this->assertTrue($schedule->fresh()->next_due_at->isFuture());
    }

    public function test_schedule_is_not_duplicated_after_next_due_date_advances(): void
    {
        $this->seed(DemoDataSeeder::class);

        $schedule = PropertyServiceSchedule::query()
            ->where('is_active', true)
            ->firstOrFail();

        $schedule->update(['next_due_at' => now()->subMinute()]);

        $service = app(PropertyServiceScheduleService::class);

        $this->assertSame(1, $service->generateDueTasks(now()));
        $this->assertSame(0, $service->generateDueTasks(now()));
    }
}
