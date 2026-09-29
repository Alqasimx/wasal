<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class OtpService
{
    public function __construct(
        protected WhatsAppOtpProvider $whatsAppProvider
    ) {
    }

    public function send(
        string $channel,
        string $destination,
        string $purpose = 'login',
        ?User $user = null
    ): OtpCode {
        if (! in_array($channel, ['whatsapp', 'email'], true)) {
            throw new InvalidArgumentException(
                'Unsupported OTP channel.'
            );
        }

        // نحذف الرموز القديمة غير المستخدمة لنفس العملية.
        OtpCode::query()
            ->where('destination', $destination)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        $code = (string) random_int(100000, 999999);

        $otp = OtpCode::create([
            'user_id' => $user?->id,
            'channel' => $channel,
            'destination' => $destination,
            'code_hash' => Hash::make($code),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
        ]);

        if ($channel === 'whatsapp') {
            $this->whatsAppProvider->send(
                $destination,
                $code
            );
        }

        if ($channel === 'email') {
            $this->sendEmailOtp(
                $destination,
                $code
            );
        }

        return $otp;
    }

    public function verify(
        string $destination,
        string $code,
        string $purpose = 'login'
    ): bool {
        $otp = OtpCode::query()
            ->where('destination', $destination)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $otp) {
            return false;
        }

        if ($otp->expires_at->isPast()) {
            return false;
        }

        if ($otp->attempts >= 5) {
            return false;
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) {
            return false;
        }

        $otp->forceFill([
            'verified_at' => now(),
        ])->save();

        return true;
    }

    protected function sendEmailOtp(
        string $email,
        string $code
    ): void {
        Mail::raw(
            "رمز التحقق الخاص بك في وصال هو: {$code}\n\nصلاحية الرمز 5 دقائق.",
            function ($message) use ($email) {
                $message
                    ->to($email)
                    ->subject('رمز التحقق - وصال');
            }
        );
    }
}