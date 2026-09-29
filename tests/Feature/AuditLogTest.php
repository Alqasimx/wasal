<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OtpService;
use App\Services\WhatsAppOtpProvider;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected object $fakeProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->fakeProvider = new class implements WhatsAppOtpProvider
        {
            public ?string $phone = null;
            public ?string $code = null;

            public function send(string $phone, string $code): void
            {
                $this->phone = $phone;
                $this->code = $code;
            }
        };

        $this->app->instance(
            WhatsAppOtpProvider::class,
            $this->fakeProvider
        );
    }

    public function test_registration_is_written_to_audit_log(): void
    {
        app(OtpService::class)->send(
            'whatsapp',
            '967700000101',
            'register'
        );

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Audit User',
            'phone' => '967700000101',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'channel' => 'whatsapp',
            'code' => $this->fakeProvider->code,
            'device_name' => 'Android',
            'platform' => 'android',
        ])->assertCreated();

        $user = User::where(
            'phone',
            '967700000101'
        )->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $user->id,
            'action' => 'auth.register',
            'entity_type' => User::class,
            'entity_id' => $user->id,
        ]);
    }

    public function test_password_login_is_written_to_audit_log(): void
    {
        $user = User::factory()->create([
            'phone' => '967700000102',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/auth/login/password', [
            'phone' => '967700000102',
            'password' => 'password123',
            'device_name' => 'iPhone',
            'platform' => 'ios',
        ])->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $user->id,
            'action' => 'auth.login_password',
            'entity_type' => User::class,
            'entity_id' => $user->id,
        ]);
    }

    public function test_password_reset_is_written_to_audit_log(): void
    {
        $user = User::factory()->create([
            'phone' => '967700000103',
            'whatsapp_phone' => '967700000103',
            'password' => 'old-password123',
            'status' => 'active',
        ]);

        app(OtpService::class)->send(
            'whatsapp',
            '967700000103',
            'password_reset',
            $user
        );

        $this->postJson('/api/v1/auth/password/reset', [
            'phone' => '967700000103',
            'channel' => 'whatsapp',
            'code' => $this->fakeProvider->code,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $user->id,
            'action' => 'auth.password_reset',
            'entity_type' => User::class,
            'entity_id' => $user->id,
        ]);
    }
}