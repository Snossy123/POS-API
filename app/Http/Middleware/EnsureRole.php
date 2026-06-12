<?php

namespace App\Http\Middleware;

use App\Support\AuthUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'غير مصرح'], 401);
        }

        $role = AuthUser::role($user);

        if (!in_array($role, $roles, true)) {
            return response()->json(['status' => 'error', 'message' => 'ليس لديك صلاحية'], 403);
        }

        return $next($request);
    }
}
