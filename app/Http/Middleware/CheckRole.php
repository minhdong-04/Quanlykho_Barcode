<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
 * Xử lý request đi vào và kiểm tra quyền truy cập theo vai trò.
 * Cách sử dụng:
 *  - ->middleware('role:admin')        : chỉ cho phép admin
 *  - ->middleware('role:admin,staff') : cho phép admin và staff
 */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (empty($roles)) {
            return $next($request);
        }

        $allowed = in_array($user->role ?? null, $roles, true);
        if (! $allowed) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
