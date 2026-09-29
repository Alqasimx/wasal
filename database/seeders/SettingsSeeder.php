<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'booking_payment_timeout_minutes',
                'value_json' => 120,
                'group' => 'booking',
                'is_public' => false,
            ],
            [
                'key' => 'default_booking_deposit_percentage',
                'value_json' => 5,
                'group' => 'booking',
                'is_public' => false,
            ],
            [
                'key' => 'default_language',
                'value_json' => 'ar',
                'group' => 'general',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value_json' => $setting['value_json'],
                    'group' => $setting['group'],
                    'is_public' => $setting['is_public'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}