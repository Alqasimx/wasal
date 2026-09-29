<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected OtpService $otpService
    ) {
    }

    public function requestOtp(
        string $phone,
        string $channel,
        ?string $email = null,
        string $purpose = 'login'
    ): void {
        $user = User::query()
            ->where('phone', $phone)
            ->first();

        $destination = match ($channel) {
            'whatsapp' => $phone,
            'email' => $email,
            default => null,
        };

        if (! $destination) {
            throw ValidationException::withMessages([
                'channel' => 'قناة التحقق غير صالحة أو بياناتها ناقصة.',
            ]);
        }

        $this->otpService->send(
            $channel,
            $destination,
            $purpose,
            $user
        );
    }

    public function registerWithOtp(
        string $name,
        string $phone,
        string $password,
        string $channel,
        string $code,
        ?string $email = null,
        ?string $whatsappPhone = null
    ): User {
        if (User::where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'رقم الهاتف مستخدم بالفعل.',
            ]);
        }

        if ($email && User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'البريد الإلكتروني مستخدم بالفعل.',
            ]);
        }

        $destination = $channel === 'email'
            ? $email
            : ($whatsappPhone ?: $phone);

        if (! $destination) {
            throw ValidationException::withMessages([
                'channel' => 'بيانات التحقق غير مكتملة.',
            ]);
        }

        $verified = $this->otpService->verify(
            $destination,
            $code,
            'register'
        );

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
            ]);
        }

        $user = User::create([
            'name' => $name,
            'phone' => $phone,
            'whatsapp_phone' => $whatsappPhone ?: $phone,
            'email' => $email,
            'password' => Hash::make($password),
            'preferred_language' => 'ar',
            'status' => 'active',
        ]);

        $user->assignRole('user');

        Auth::login($user);

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        return $user;
    }

    public function loginWithOtp(
        string $phone,
        string $channel,
        string $code,
        ?string $email = null
    ): User {
        $user = User::query()
            ->where('phone', $phone)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => 'الحساب غير موجود.',
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'phone' => 'الحساب غير نشط.',
            ]);
        }

        $destination = $channel === 'email'
            ? ($email ?: $user->email)
            : ($user->whatsapp_phone ?: $user->phone);

        if (! $destination) {
            throw ValidationException::withMessages([
                'channel' => 'لا توجد وسيلة تحقق متاحة لهذا الحساب.',
            ]);
        }

        $verified = $this->otpService->verify(
            $destination,
            $code,
            'login'
        );

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
            ]);
        }

        Auth::login($user);

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        return $user;
    }

    public function loginWithPassword(
        string $phone,
        string $password
    ): User {
        $user = User::query()
            ->where('phone', $phone)
            ->first();

        if (
            ! $user ||
            ! Hash::check($password, $user->password)
        ) {
            throw ValidationException::withMessages([
                'phone' => 'رقم الهاتف أو كلمة المرور غير صحيحة.',
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'phone' => 'الحساب غير نشط.',
            ]);
        }

        Auth::login($user);

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        return $user;
    }

    public function resetPasswordWithOtp(
        string $phone,
        string $channel,
        string $code,
        string $newPassword,
        ?string $email = null
    ): User {
        $user = User::query()
            ->where('phone', $phone)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => 'الحساب غير موجود.',
            ]);
        }

        $destination = $channel === 'email'
            ? ($email ?: $user->email)
            : ($user->whatsapp_phone ?: $user->phone);

        if (! $destination) {
            throw ValidationException::withMessages([
                'channel' => 'لا توجد وسيلة تحقق متاحة لهذا الحساب.',
            ]);
        }

        $verified = $this->otpService->verify(
            $destination,
            $code,
            'password_reset'
        );

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();

        return $user;
    }

    public function logout(): void
    {
        Auth::logout();
    }
}