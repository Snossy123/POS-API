<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Support\AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmployeeAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $employee = Employee::where('email', $credentials['email'])->first();

        if (!$employee || !$employee->active) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة أو الحساب غير نشط'],
            ]);
        }

        $valid = Hash::check($credentials['password'], $employee->password)
            || $employee->password === $credentials['password'];

        if (!$valid) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة'],
            ]);
        }

        if ($employee->password === $credentials['password']) {
            $employee->password = $credentials['password'];
            $employee->save();
        }

        $token = $employee->createToken('employee-api')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => AuthUser::toArray($employee),
        ]);
    }
}
