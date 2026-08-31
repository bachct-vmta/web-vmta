<?php

namespace Packages\Core\Src\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string|null  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $user = $request->user();

        // Not logged in
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        // Check if user is active
        if (! $user->is_active) {
            auth()->logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account has been deactivated.'], 403);
            }

            return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa.');
        }

        // Super user bypasses all checks
        if ($user->isSuperUser()) {
            return $next($request);
        }

        // If permission is false or 'false', skip check
        if ($permission === 'false' || $permission === false) {
            return $next($request);
        }

        // If permission is 'superuser', only allow super users
        if ($permission === 'superuser') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Super user access required.'], 403);
            }
            abort(403, 'Chỉ Super User mới có quyền truy cập.');
        }

        // Get permission from route name if not specified
        if (empty($permission)) {
            $permission = $request->route()->getName();
        }

        // Check permission
        if (! $user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        return $next($request);
    }
}
