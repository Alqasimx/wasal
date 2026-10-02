<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_due_within_24_hours_creates_one_in_app_notification(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 10:00:00'));
        $assignee = User::factory()->create();
        $creator = User::factory()->create();

        $task = Task::create([
            'title' => 'تنظيف الألواح الشمسية',
            'assigned_to_user_id' => $assignee->id,
            'created_by_user_id' => $creator->id,
            'due_at' => now()->addHours(20),
            'recurrence' => Task::RECURRENCE_WEEKLY,
            'notify_before_minutes' => 1440,
        ]);

        $service = app(TaskNotificationService::class);

        $this->assertSame(1, $service->notifyDueTasks());
        $this->assertDatabaseHas('notifications', [
            'user_id' => $assignee->id,
            'type' => 'task_due_soon',
        ]);
        $this->assertNotNull($task->fresh()->notified_at);
        $this->assertSame(0, $service->notifyDueTasks());
        $this->assertSame(1, Notification::count());

        Carbon::setTestNow();
    }

    public function test_completed_and_cancelled_tasks_are_not_notified(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        foreach ([Task::STATUS_COMPLETED, Task::STATUS_CANCELLED] as $status) {
            Task::create([
                'title' => $status,
                'assigned_to_user_id' => $assignee->id,
                'created_by_user_id' => $creator->id,
                'due_at' => now()->addHour(),
                'status' => $status,
            ]);
        }

        $this->assertSame(0, app(TaskNotificationService::class)->notifyDueTasks());
        $this->assertDatabaseCount('notifications', 0);
    }
}
