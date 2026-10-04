<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskNotificationService;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DemoAlertSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_alerts_start_after_ten_minutes_then_arrive_each_minute(): void
    {
        Carbon::setTestNow('2026-10-04 22:00:00');

        $this->seed(DemoDataSeeder::class);

        $user = User::query()
            ->where('email', 'demo.manager@wasal.local')
            ->firstOrFail();

        Notification::query()->delete();

        Task::query()->update([
            'notified_at' => now(),
        ]);

        $this->artisan('wasal:seed-demo-alert-sequence', [
            '--email' => $user->email,
        ])->assertSuccessful();

        $tasks = Task::query()
            ->where('assigned_to_user_id', $user->id)
            ->where('description', 'like', 'DEMO-ALERT-SEQUENCE:%')
            ->orderBy('due_at')
            ->get();

        $this->assertCount(10, $tasks);
        $this->assertSame('2026-10-04 22:10', $tasks->first()->due_at->format('Y-m-d H:i'));
        $this->assertSame('2026-10-04 22:19', $tasks->last()->due_at->format('Y-m-d H:i'));

        $service = app(TaskNotificationService::class);

        $this->assertSame(0, $service->notifyDueTasks(now()->addMinutes(9)));
        $this->assertSame(1, $service->notifyDueTasks(now()->addMinutes(10)));
        $this->assertSame(1, $service->notifyDueTasks(now()->addMinutes(11)));

        $this->assertSame(2, Notification::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['tenancy_due', 'rent_due'])
            ->count());

        Carbon::setTestNow();
    }
}
