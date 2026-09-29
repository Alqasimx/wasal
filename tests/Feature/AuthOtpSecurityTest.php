<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthOtpSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_otp_cannot_be_sent_to_an_email_not_owned_by_account(): void
    {
        User::create([
            'name' => 'Test User',
            'phone' => '777000001',
            'whatsapp_phone' => '777000002',
            'email' => 'owner@example.com',
            'password' => Hash::make('password123'),
            'preferred_language' => 'ar',
            'status' => 'active',
        ]);

        $this->expectException(ValidationException::class);

        app(AuthService::class)->requestOtp(
            phone: '777000001',
            channel: 'email',
            email: 'attacker@example.com',
            purpose: 'login'
        );
    }

    public function test_password_reset_otp_cannot_be_sent_to_an_email_not_owned_by_account(): void
    {
        User::create([
            'name' => 'Test User',
            'phone' => '777000003',
            'whatsapp_phone' => '777000004',
            'email' => 'owner2@example.com',
            'password' => Hash::make('password123'),
            'preferred_language' => 'ar',
            'status' => 'active',
        ]);

        $this->expectException(ValidationException::class);

        app(AuthService::class)->requestOtp(
            phone: '777000003',
            channel: 'email',
            email: 'attacker@example.com',
            purpose: 'password_reset'
        );
    }

    public function test_login_whatsapp_otp_is_sent_to_registered_whatsapp_number(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'phone' => '777000005',
            'whatsapp_phone' => '777999999',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'preferred_language' => 'ar',
            'status' => 'active',
        ]);

        app(AuthService::class)->requestOtp(
            phone: $user->phone,
            channel: 'whatsapp',
            purpose: 'login'
        );

        $this->assertDatabaseHas('otp_codes', [
            'user_id' => $user->id,
            'channel' => 'whatsapp',
            'destination' => '777999999',
            'purpose' => 'login',
        ]);
    }

    public function test_registration_whatsapp_otp_uses_requested_whatsapp_number(): void
    {
        app(AuthService::class)->requestOtp(
            phone: '777000006',
            channel: 'whatsapp',
            purpose: 'register',
            whatsappPhone: '777888888'
        );

        $this->assertDatabaseHas('otp_codes', [
            'user_id' => null,
            'channel' => 'whatsapp',
            'destination' => '777888888',
            'purpose' => 'register',
        ]);
    }
}