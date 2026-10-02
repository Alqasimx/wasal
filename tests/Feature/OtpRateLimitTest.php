<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class OtpRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear(
            'otp:register:whatsapp:777123456'
        );
    }

    public function test_otp_request_is_rate_limited_after_three_attempts(): void
    {
        $payload = [
            'phone' => '777123456',
            'channel' => 'whatsapp',
            'purpose' => 'register',
        ];

        $this->postJson(
            '/api/v1/auth/otp/request',
            $payload
        )->assertOk();

        $this->postJson(
            '/api/v1/auth/otp/request',
            $payload
        )->assertOk();

        $this->postJson(
            '/api/v1/auth/otp/request',
            $payload
        )->assertOk();

        $response = $this->postJson(
            '/api/v1/auth/otp/request',
            $payload
        );

        $response
            ->assertStatus(429)
            ->assertJsonStructure([
                'message',
                'retry_after_seconds',
            ]);
    }
}