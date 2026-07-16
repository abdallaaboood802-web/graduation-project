<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\BookResource; // 👈 تم استيراده لتشغيل جلب الكتب في البروفايل
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule; // 👈 تم استيراده لاستخدامه في التحقق عند التحديث

/**
 * متحكم المصادقة – تسجيل الدخول والخروج وبيانات المستخدم والملف الشخصي.
 */
class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     *
     * تسجيل الدخول وإعادة Sanctum Token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        // ملاحظة: تم استخدام password_hash هنا بناءً على بنية جدولك
        if (! $user || ! Hash::check($credentials['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الاعتماد غير صحيحة.'],
            ]);
        }

        // إلغاء جميع التوكنات القديمة (جلسة واحدة نشطة)
        $user->tokens()->delete();

        $token = $user->createToken(
            name: 'api-token',
            abilities: ['*'],
            expiresAt: now()->addDays(30),
        );

        return response()->json([
            'message'    => 'تم تسجيل الدخول بنجاح.',
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user'       => new UserResource($user),
        ]);
    }

    /**
     * POST /api/auth/logout
     *
     * تسجيل الخروج وحذف التوكن الحالي.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }

    /**
     * GET /api/me
     *
     * جلب بيانات المستخدم الحالي.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('authorProfile');

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * POST /api/auth/register
     *
     * تسجيل مستخدم جديد وإعادة Sanctum Token مباشرةً.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'username'          => $request->validated('username'),
            'email'             => $request->validated('email'),
            'password_hash'     => $request->validated('password'), // Cast 'hashed' يشفّره تلقائياً
            'role'              => \App\Enums\UserRole::Reader,      // دور القارئ الافتراضي
            'bio'               => $request->validated('bio'),
            'email_verified_at' => now(),
        ]);

        // إعادة تحميل المستخدم من قاعدة البيانات لتطبيق جميع الـ Casts بشكل صحيح
        $user->refresh();

        $token = $user->createToken(
            name: 'api-token',
            abilities: ['*'],
            expiresAt: now()->addDays(30),
        );

        return response()->json([
            'message'    => 'تم إنشاء حسابك بنجاح، مرحباً بك في إشراق!',
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user'       => new UserResource($user),
        ], 201);
    }

    /**
     * GET /api/v1/user/profile
     *
     * جلب بيانات الملف الشخصي للمستخدم الحالي مع كتبه.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // جلب الكتب التي رفعها هذا المستخدم تحديداً
        $books = $user->books()->latest()->get(); 

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->username, // 👈 تم تعديلها إلى username لتطابق جدولك
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'bio' => $user->bio,
                'created_at' => $user->created_at ? $user->created_at->format('Y-m-d') : null,
            ],
            'books' => BookResource::collection($books)
        ]);
    }

    /**
     * PUT /api/v1/user/profile
     *
     * تحديث بيانات الملف الشخصي.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'], // يمثل الـ username المعروض بالفرونت
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'bio' => ['nullable', 'string', 'max:1000']
        ]);

        $user->username = $validated['name']; // 👈 حفظ في الحقل الصحيح username
        $user->email = $validated['email'];
        $user->bio = $validated['bio'] ?? $user->bio;

        if (!empty($validated['password'])) {
            // 👈 التشفير والحفظ في حقل password_hash المستخدم في مشروعك
            $user->password_hash = Hash::make($validated['password']); 
        }

        $user->save();

        return response()->json([
            'message' => 'تم تحديث الملف الشخصي بنجاح.',
            'user' => [
                'id' => $user->id,
                'name' => $user->username,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'bio' => $user->bio,
                'created_at' => $user->created_at ? $user->created_at->format('Y-m-d') : null,
            ]
        ]);
    }
}