<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FakeWhatsAppOtpProvider implements WhatsAppOtpProvider
{
    public function send(string $phone, string $code): void
    {
        Log::info('Fake WhatsApp OTP', [
            'phone' => $phone,
            'code' => $code,
        ]);
    }
}