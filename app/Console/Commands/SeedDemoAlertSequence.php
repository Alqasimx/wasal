<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class SeedDemoAlertSequence extends Command
{
    protected $signature = 'wasal:seed-demo-alert-sequence {--email=}';

    protected $description = 'Create ten demo alerts, starting after ten minutes, one per minute';

    public function handle(): int
    {
        $email = $this->option('email');

        $recipients = $email
            ? User::query()->where('email', $email)->where('status', 'active')->get()
            : User::role(['system_admin', 'property_management'])
                ->where('status', 'active')
                ->get();

        if ($recipients->isEmpty()) {
            $this->error('No active recipient found.');

            return self::FAILURE;
        }

        $sequence = [
            ['tenancy', 'تنبيه عقد إيجار', 'مراجعة عقد إيجار يقترب من موعد متابعة.'],
            ['rent_due_item', 'تنبيه تحصيل إيجار', 'متابعة استحقاق إيجار يحتاج تحصيلًا.'],
            ['maintenance_request', 'تنبيه صيانة كهرباء', 'طلب فحص كهرباء يحتاج متابعة.'],
            ['maintenance_request', 'تنبيه صيانة سباكة', 'طلب سباكة مجدول ويحتاج متابعة.'],
            ['property_service_schedule', 'تنبيه تنظيف ألواح شمسية', 'موعد خدمة تنظيف ألواح شمسية اقترب.'],
            ['property_expense', 'تنبيه مصروف عقار', 'يوجد مصروف عقار يحتاج مراجعة.'],
            ['property_management_agreement', 'تنبيه اتفاق إدارة', 'اتفاق إدارة يحتاج مراجعة أو تجديد.'],
            ['property_vendor', 'تنبيه مورد / فني', 'يوجد مورد أو فني يحتاج متابعة.'],
            ['property_unit', 'تنبيه إشغال وحدة', 'حالة إشغال وحدة تحتاج مراجعة.'],
            ['owner_settlement', 'تنبيه تسوية مالك', 'تسوية مالك جديدة تحتاج مراجعة واعتماد.'],
        ];

        foreach ($recipients as $recipient) {
            Task::query()
                ->where('assigned_to_user_id', $recipient->id)
                ->where('description', 'like', 'DEMO-ALERT-SEQUENCE:%')
                ->delete();

            foreach ($sequence as $index => [$type, $title, $body]) {
                Task::create([
                    'title' => $title,
                    'description' => 'DEMO-ALERT-SEQUENCE: '.$body,
                    'related_type' => $type,
                    'related_id' => null,
                    'assigned_to_user_id' => $recipient->id,
                    'created_by_user_id' => $recipient->id,
                    'due_at' => now()->addMinutes(10 + $index),
                    'recurrence' => Task::RECURRENCE_ONCE,
                    'status' => Task::STATUS_PENDING,
                    'notify_before_minutes' => 0,
                    'notified_at' => null,
                ]);
            }

            $this->info(
                'Alert sequence scheduled for '.$recipient->email
                .' from '.now()->addMinutes(10)->format('H:i')
                .' through '.now()->addMinutes(19)->format('H:i')
            );
        }

        return self::SUCCESS;
    }
}
