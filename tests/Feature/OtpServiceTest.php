<?php

namespace Tests\Feature;

use App\Services\OtpService;
use App\Services\WhatsAppOtpProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_send_and_verify_whatsapp_otp(): void
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

        $service = app(OtpService::class);

        $otp = $service->send(
            'whatsapp',
            '967777777777',
            'login'
        );

        $this->assertSame(
            '967777777777',
            $fakeProvider->phone
        );

        $this->assertNotNull($fakeProvider->code);

        $this->assertSame(
            6,
            strlen($fakeProvider->code)
        );

        $this->assertFalse(
            $service->verify(
                '967777777777',
                '000000',
                'login'
            )
        );

        $this->assertTrue(
            $service->verify(
                '967777777777',
                $fakeProvider->code,
                'login'
            )
        );

        $otp->refresh();

        $this->assertSame(2, $otp->attempts);

        $this->assertNotNull(
            $otp->verified_at
        );
    }

    public function test_expired_otp_cannot_be_verified(): void
    {
        $fakeProvider = new class implements WhatsAppOtpProvider
        {
            public ?string $code = null;

            public function send(string $phone, string $code): void
            {
                $this->code = $code;
            }
        };

        $this->app->instance(
            WhatsAppOtpProvider::class,
            $fakeProvider
        );

        $service = app(OtpService::class);

        $otp = $service->send(
            'whatsapp',
            '967711111111',
            'login'
        );

        $otp->forceFill([
            'expires_at' => now()->subMinute(),
        ])->save();

        $this->assertFalse(
            $service->verify(
                '967711111111',
                $fakeProvider->code,
                'login'
            )
        );
    }

    public function test_otp_is_blocked_after_five_failed_attempts(): void
    {
        $fakeProvider = new class implements WhatsAppOtpProvider
        {
            public ?string $code = null;

            public function send(string $phone, string $code): void
            {
                $this->code = $code;
            }
        };

        $this->app->instance(
            WhatsAppOtpProvider::class,
            $fakeProvider
        );

        $service = app(OtpService::class);

        $service->send(
            'whatsapp',
            '967722222222',
            'login'
        );

        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(
                $service->verify(
                    '967722222222',
                    '000000',
                    'login'
                )
            );
        }

        $this->assertFalse(
            $service->verify(
                '967722222222',
                $fakeProvider->code,
                'login'
            )
        );
    }
}