<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Services\WhatsAppOtpProvider;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function fakeWhatsAppProvider(): object
    {
        $fakeProvider = new class implements WhatsAppOtpProvider
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
            $fakeProvider
        );

        return $fakeProvider;
    }

    public function test_user_can_register_with_whatsapp_otp(): void
    {
        $fakeProvider = $this->fakeWhatsAppProvider();

        $otpService = app(OtpService::class);
        $authService = app(AuthService::class);

        $otpService->send(
            'whatsapp',
            '967733333333',
            'register'
        );

        $user = $authService->registerWithOtp(
            name: 'Test User',
            phone: '967733333333',
            password: 'password123',
            channel: 'whatsapp',
            code: $fakeProvider->code
        );

        $this->assertDatabaseHas('users', [
            'phone' => '967733333333',
            'name' => 'Test User',
        ]);

        $this->assertTrue(
            Hash::check(
                'password123',
                $user->password
            )
        );

        $this->assertTrue(
            $user->hasRole('user')
        );

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_otp(): void
    {
        $fakeProvider = $this->fakeWhatsAppProvider();

        $user = User::factory()->create([
            'phone' => '967744444444',
            'whatsapp_phone' => '967744444444',
            'status' => 'active',
        ]);

        $otpService = app(OtpService::class);
        $authService = app(AuthService::class);

        $otpService->send(
            'whatsapp',
            '967744444444',
            'login',
            $user
        );

        $loggedInUser = $authService->loginWithOtp(
            phone: '967744444444',
            channel: 'whatsapp',
            code: $fakeProvider->code
        );

        $this->assertSame(
            $user->id,
            $loggedInUser->id
        );

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_password(): void
    {
        $user = User::factory()->create([
            'phone' => '967755555555',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $authService = app(AuthService::class);

        $loggedInUser = $authService->loginWithPassword(
            '967755555555',
            'password123'
        );

        $this->assertSame(
            $user->id,
            $loggedInUser->id
        );

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create([
            'phone' => '967766666666',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $this->expectException(
            ValidationException::class
        );

        app(AuthService::class)->loginWithPassword(
            '967766666666',
            'wrong-password'
        );
    }
}