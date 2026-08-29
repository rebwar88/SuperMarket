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

        // ١. ئەگەر بەکارهێنەرەکە مۆڵەتەکەی لەڕێگەی یەکێک لە ڕۆڵەکانی لە داتابەیس پێدرابێت
        $hasPermission = DB::table('model_has_roles')
            ->where(function($q) use ($user) {
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

        // ٢. پشکنین ئەگەر پارامیتەرەکە ناوی ڕۆڵ بێت لەبری مۆڵەت
        $hasRole = DB::table('model_has_roles')
            ->where(function($q) use ($user) {
                $q->where('model_uuid', $user->id)
                  ->orWhere('model_uuid', (string) $user->id);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', $permissionOrRole)
            ->exists();

        if ($hasRole) {
            return $next($request);
        }

        // ٣. تێپەڕاندنی فەرمی بەکارهێنەر ئەگەر خاوەنی زۆرینەی مۆڵەتەکان بێت (وەک ڕۆڵی خاوەن یان Admin)
        $userRolePermsCount = DB::table('model_has_roles')
            ->where(function($q) use ($user) {
                $q->where('model_uuid', $user->id)
                  ->orWhere('model_uuid', (string) $user->id);
            })
            ->join('role_has_permissions', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->count();

        if ($userRolePermsCount >= 100) {
            return $next($request);
        }

        // ئەگەر کاشێر بێت ڕەوانەی سندوق دەکرێت
        $isCashier = DB::table('model_has_roles')
            ->where(function($q) use ($user) {
                $q->where('model_uuid', $user->id)
                  ->orWhere('model_uuid', (string) $user->id);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn(DB::raw('LOWER(roles.name)'), ['cashier', 'کاشێر'])
            ->exists();

        if ($isCashier) {
            return redirect()->route('pos.index');
        }

        abort(403, 'دەسەڵاتی پێویستت نییە بۆ دەستپێگەیشتن بەم بەشە.');
    }
}
