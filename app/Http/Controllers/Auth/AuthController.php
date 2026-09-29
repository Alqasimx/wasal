<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function requestOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'channel' => ['required', 'in:whatsapp,email'],
            'email' => ['nullable', 'email'],
            'purpose' => ['required', 'in:register,login,password_reset'],
        ]);

        if (
            $data['channel'] === 'email' &&
            empty($data['email'])
        ) {
            return response()->json([
                'message' => 'البريد الإلكتروني مطلوب عند اختيار التحقق عبر البريد.',
            ], 422);
        }

        $this->authService->requestOtp(
            $data['phone'],
            $data['channel'],
            $data['email'] ?? null,
            $data['purpose']
        );

        return response()->json([
            'message' => 'تم إرسال رمز التحقق.',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'channel' => ['required', 'in:whatsapp,email'],
            'code' => ['required', 'digits:6'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $this->authService->registerWithOtp(
            $data['name'],
            $data['phone'],
            $data['password'],
            $data['channel'],
            $data['code'],
            $data['email'] ?? null,
            $data['whatsapp_phone'] ?? null
        );

        $apiSession = $this->authService->createApiSession(
            $user,
            $request,
            $data['device_name'] ?? 'api',
            $data['platform'] ?? null
        );

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح.',
            'token_type' => 'Bearer',
            'token' => $apiSession['token'],
            'user' => $user,
        ], 201);
    }

    public function loginOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'channel' => ['required', 'in:whatsapp,email'],
            'email' => ['nullable', 'email'],
            'code' => ['required', 'digits:6'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $this->authService->loginWithOtp(
            $data['phone'],
            $data['channel'],
            $data['code'],
            $data['email'] ?? null
        );

        $apiSession = $this->authService->createApiSession(
            $user,
            $request,
            $data['device_name'] ?? 'api',
            $data['platform'] ?? null
        );

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح.',
            'token_type' => 'Bearer',
            'token' => $apiSession['token'],
            'user' => $user,
        ]);
    }

    public function loginPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $this->authService->loginWithPassword(
            $data['phone'],
            $data['password']
        );

        $apiSession = $this->authService->createApiSession(
            $user,
            $request,
            $data['device_name'] ?? 'api',
            $data['platform'] ?? null
        );

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح.',
            'token_type' => 'Bearer',
            'token' => $apiSession['token'],
            'user' => $user,
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'channel' => ['required', 'in:whatsapp,email'],
            'email' => ['nullable', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->authService->resetPasswordWithOtp(
            $data['phone'],
            $data['channel'],
            $data['code'],
            $data['password'],
            $data['email'] ?? null
        );

        $user->tokens()->delete();

        $user->authSessions()
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);

        return response()->json([
            'message' => 'تم تغيير كلمة المرور بنجاح. يرجى تسجيل الدخول مرة أخرى.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $authorization = $request->header('Authorization');

        $plainTextToken = null;

        if (
            $authorization &&
            str_starts_with($authorization, 'Bearer ')
        ) {
            $plainTextToken = substr(
                $authorization,
                7
            );
        }

        $this->authService->revokeCurrentApiSession(
            $user,
            $plainTextToken
        );

        $accessToken = $user?->currentAccessToken();

        if (
            $accessToken &&
            method_exists($accessToken, 'delete')
        ) {
            $accessToken->delete();
        }

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }
}