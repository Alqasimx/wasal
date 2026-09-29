<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OtpService;
use App\Services\WhatsAppOtpProvider;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
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

    public function test_can_request_whatsapp_otp(): void
    {
        $response = $this->postJson('/api/v1/auth/otp/request', [
            'phone' => '967700000001',
            'channel' => 'whatsapp',
            'purpose' => 'register',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'تم إرسال رمز التحقق.',
            ]);

        $this->assertDatabaseHas('otp_codes', [
            'destination' => '967700000001',
            'channel' => 'whatsapp',
            'purpose' => 'register',
        ]);

        $this->assertSame(
            '967700000001',
            $this->fakeProvider->phone
        );

        $this->assertNotNull(
            $this->fakeProvider->code
        );
    }

    public function test_can_register_with_whatsapp_otp(): void
    {
        app(OtpService::class)->send(
            'whatsapp',
            '967700000002',
            'register'
        );

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'New User',
            'phone' => '967700000002',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'channel' => 'whatsapp',
            'code' => $this->fakeProvider->code,
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'تم إنشاء الحساب بنجاح.',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'phone' => '967700000002',
        ]);

        $user = User::where(
            'phone',
            '967700000002'
        )->first();

        $this->assertNotNull($user);

        $this->assertTrue(
            $user->hasRole('user')
        );
    }

    public function test_can_login_with_whatsapp_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '967700000003',
            'whatsapp_phone' => '967700000003',
            'status' => 'active',
        ]);

        app(OtpService::class)->send(
            'whatsapp',
            '967700000003',
            'login',
            $user
        );

        $response = $this->postJson('/api/v1/auth/login/otp', [
            'phone' => '967700000003',
            'channel' => 'whatsapp',
            'code' => $this->fakeProvider->code,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'تم تسجيل الدخول بنجاح.',
            ]);
    }

    public function test_can_login_with_password(): void
    {
        User::factory()->create([
            'phone' => '967700000004',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login/password', [
            'phone' => '967700000004',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'تم تسجيل الدخول بنجاح.',
            ]);
    }

    public function test_wrong_password_returns_validation_error(): void
    {
        User::factory()->create([
            'phone' => '967700000005',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login/password', [
            'phone' => '967700000005',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'phone',
            ]);
    }

    public function test_email_is_required_when_email_channel_is_selected(): void
    {
        $response = $this->postJson('/api/v1/auth/otp/request', [
            'phone' => '967700000006',
            'channel' => 'email',
            'purpose' => 'register',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'البريد الإلكتروني مطلوب عند اختيار التحقق عبر البريد.',
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'phone' => '967700000007',
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'تم تسجيل الخروج بنجاح.',
            ]);
    }

    public function test_user_can_reset_password_with_whatsapp_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '967700000008',
            'whatsapp_phone' => '967700000008',
            'password' => 'old-password123',
            'status' => 'active',
        ]);

        app(OtpService::class)->send(
            'whatsapp',
            '967700000008',
            'password_reset',
            $user
        );

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'phone' => '967700000008',
            'channel' => 'whatsapp',
            'code' => $this->fakeProvider->code,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'تم تغيير كلمة المرور بنجاح.',
            ]);

        $user->refresh();

        $this->assertTrue(
            Hash::check(
                'new-password123',
                $user->password
            )
        );
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'phone' => '967700000009',
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user');

        $response
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('phone', '967700000009');
    }
}