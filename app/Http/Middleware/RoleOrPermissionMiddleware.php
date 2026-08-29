<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RoleOrPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permissionOrRole): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // بەدەستهێنانی ڕۆڵەکانی ئەم بەکارهێنەرە لە داتابەیس
        $userRoles = DB::table('model_has_roles')
            ->where(function ($q) use ($user) {
                $q->where('model_uuid', $user->id)
                  ->orWhere('model_uuid', (string) $user->id);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->pluck('roles.name')
            ->map(fn ($r) => strtolower((string) $r))
            ->toArray();

        // ڕێگەپێدانی گشتی بۆ خاوەن و بەڕێوەبەری سەرەکی
        if (in_array('خاوەن', $userRoles, true) || in_array('super_admin', $userRoles, true) || in_array('admin', $userRoles, true) || in_array('super admin', $userRoles, true)) {
            return $next($request);
        }

        // پشکنین بەپێی ڕۆڵی دیاریکراو
        if (in_array(strtolower($permissionOrRole), $userRoles, true)) {
            return $next($request);
        }

        // پشکنین بەپێی مۆڵەتی دیاریکراو
        $hasPermission = DB::table('model_has_roles')
            ->where(function ($q) use ($user) {
                $q->where('model_uuid', $user->id)
                  ->orWhere('model_uuid', (string) $user->id);
            })
            ->join('role_has_permissions', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('permissions.name', $permissionOrRole)
            ->exists();

        if ($hasPermission) {
            return $next($request);
        }

        // ئەگەر کاشێر بێت و مۆڵەتی نەبێت بۆ ئەم بەشە، ڕەوانەی POS دەکرێت
        if (in_array('cashier', $userRoles, true) || in_array('کاشێر', $userRoles, true)) {
            return redirect()->route('pos.index');
        }

        abort(403, 'دەسەڵاتی پێویستت نییە بۆ دەستپێگەیشتن بەم بەشە.');
    }
}
