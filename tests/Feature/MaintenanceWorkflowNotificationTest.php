<?php

namespace Tests\Feature;

use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Task;
use App\Services\MaintenanceWorkflowService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceWorkflowNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_urgent_maintenance_creates_task_and_immediate_alert(): void
    {
        $this->seed(DemoDataSeeder::class);

        $request = MaintenanceRequest::query()
            ->where('reference_number', 'DEMO-MNT-002')
            ->firstOrFail();

        Task::query()
            ->where('related_type', 'maintenance_request')
            ->where('related_id', $request->id)
            ->delete();

        Notification::query()
            ->where('type', 'maintenance_urgent')
            ->delete();

        $task = app(MaintenanceWorkflowService::class)
            ->syncTask($request->fresh());

        $this->assertNotNull($task);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'related_type' => 'maintenance_request',
            'related_id' => $request->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $request->assigned_to_user_id,
            'type' => 'maintenance_urgent',
            'title' => 'صيانة عاجلة: '.$request->title,
        ]);
    }
}
