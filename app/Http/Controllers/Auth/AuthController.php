<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuthUser;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => AuthUser::toArray($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}
