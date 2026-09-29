<?php

namespace App\Services;

use App\Models\AuthSession;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
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
        string $purpose = 'login',
        ?string $whatsappPhone = null
    ): void {
        $user = User::query()
            ->where('phone', $phone)
            ->first();

        if ($purpose === 'register') {
            if ($user) {
                throw ValidationException::withMessages([
                    'phone' => 'رقم الهاتف مستخدم بالفعل.',
                ]);
            }

            if (
                $email &&
                User::query()->where('email', $email)->exists()
            ) {
                throw ValidationException::withMessages([
                    'email' => 'البريد الإلكتروني مستخدم بالفعل.',
                ]);
            }

            $destination = match ($channel) {
                'whatsapp' => $whatsappPhone ?: $phone,
                'email' => $email,
                default => null,
            };

            if (! $destination) {
                throw ValidationException::withMessages([
                    'channel' => 'بيانات التحقق غير مكتملة.',
                ]);
            }

            $this->otpService->send(
                $channel,
                $destination,
                'register'
            );

            return;
        }

        if (! in_array($purpose, ['login', 'password_reset'], true)) {
            throw ValidationException::withMessages([
                'purpose' => 'غرض رمز التحقق غير صالح.',
            ]);
        }

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

        if ($channel === 'email') {
            if (! $user->email) {
                throw ValidationException::withMessages([
                    'email' => 'لا يوجد بريد إلكتروني مرتبط بهذا الحساب.',
                ]);
            }

            if (! $email || strcasecmp($email, $user->email) !== 0) {
                throw ValidationException::withMessages([
                    'email' => 'البريد الإلكتروني لا يطابق البريد المسجل في الحساب.',
                ]);
            }

            $destination = $user->email;
        } elseif ($channel === 'whatsapp') {
            $destination = $user->whatsapp_phone ?: $user->phone;
        } else {
            throw ValidationException::withMessages([
                'channel' => 'قناة التحقق غير صالحة.',
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
        if (User::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'رقم الهاتف مستخدم بالفعل.',
            ]);
        }

        if (
            $email &&
            User::query()->where('email', $email)->exists()
        ) {
            throw ValidationException::withMessages([
                'email' => 'البريد الإلكتروني مستخدم بالفعل.',
            ]);
        }

        $destination = match ($channel) {
            'email' => $email,
            'whatsapp' => $whatsappPhone ?: $phone,
            default => null,
        };

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
            'email_verified_at' => $channel === 'email' ? now() : null,
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

        if ($channel === 'email') {
            if (! $user->email) {
                throw ValidationException::withMessages([
                    'email' => 'لا يوجد بريد إلكتروني مرتبط بهذا الحساب.',
                ]);
            }

            if (! $email || strcasecmp($email, $user->email) !== 0) {
                throw ValidationException::withMessages([
                    'email' => 'البريد الإلكتروني لا يطابق البريد المسجل في الحساب.',
                ]);
            }

            $destination = $user->email;
        } elseif ($channel === 'whatsapp') {
            $destination = $user->whatsapp_phone ?: $user->phone;
        } else {
            throw ValidationException::withMessages([
                'channel' => 'قناة التحقق غير صالحة.',
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

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'phone' => 'الحساب غير نشط.',
            ]);
        }

        if ($channel === 'email') {
            if (! $user->email) {
                throw ValidationException::withMessages([
                    'email' => 'لا يوجد بريد إلكتروني مرتبط بهذا الحساب.',
                ]);
            }

            if (! $email || strcasecmp($email, $user->email) !== 0) {
                throw ValidationException::withMessages([
                    'email' => 'البريد الإلكتروني لا يطابق البريد المسجل في الحساب.',
                ]);
            }

            $destination = $user->email;
        } elseif ($channel === 'whatsapp') {
            $destination = $user->whatsapp_phone ?: $user->phone;
        } else {
            throw ValidationException::withMessages([
                'channel' => 'قناة التحقق غير صالحة.',
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

    public function createApiSession(
        User $user,
        Request $request,
        ?string $deviceName = null,
        ?string $platform = null
    ): array {
        $tokenName = $deviceName ?: 'api';

        $plainTextToken = $user
            ->createToken($tokenName)
            ->plainTextToken;

        $tokenHash = hash('sha256', $plainTextToken);

        $device = UserDevice::create([
            'user_id' => $user->id,
            'device_name' => $deviceName,
            'platform' => $platform,
            'last_seen_at' => now(),
        ]);

        $session = AuthSession::create([
            'user_id' => $user->id,
            'token_hash' => $tokenHash,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_activity_at' => now(),
            'expires_at' => null,
        ]);

        return [
            'token' => $plainTextToken,
            'device' => $device,
            'session' => $session,
        ];
    }

    public function revokeCurrentApiSession(
        User $user,
        ?string $plainTextToken = null
    ): void {
        if (! $plainTextToken) {
            return;
        }

        $tokenHash = hash('sha256', $plainTextToken);

        AuthSession::query()
            ->where('user_id', $user->id)
            ->where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }

    public function logout(): void
    {
        Auth::logout();
    }
}