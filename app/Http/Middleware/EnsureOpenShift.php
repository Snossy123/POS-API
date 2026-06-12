<?php

namespace App\Http\Middleware;

use App\Models\Shift;
use App\Support\AuthUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOpenShift
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof Employee) {
            return $next($request);
        }

        $shift = Shift::where('employee_id', $user->id)
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if (!$shift) {
            return response()->json([
                'status' => 'error',
                'message' => 'يجب فتح وردية قبل إتمام البيع',
                'code' => 'SHIFT_REQUIRED',
            ], 422);
        }

        $request->attributes->set('current_shift', $shift);

        return $next($request);
    }
}
