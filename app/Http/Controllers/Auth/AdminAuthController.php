<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة'],
            ]);
        }

        try {
            $token = $user->createToken('admin-api')->plainTextToken;
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'تعذر إنشاء جلسة الدخول. تحقق من APP_KEY وجداول قاعدة البيانات.',
                'error' => 'token_creation_failed',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => AuthUser::toArray($user),
        ]);
    }
}
