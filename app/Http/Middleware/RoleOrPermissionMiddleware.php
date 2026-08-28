<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleOrPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permissionOrRole): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // بەکارهێنەری یەکەم یان ئەوانەی ڕۆڵی super_admin / admin ـیان هەیە هەموو دەسەڵاتێکیان هەیە
        if ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->id === \App\Domains\Auth\Models\User::first()?->id) {
            return $next($request);
        }

        // پشکنینی پارێزراو بە can یان hasRole
        try {
            if ($user->can($permissionOrRole) || $user->hasRole($permissionOrRole)) {
                return $next($request);
            }
        } catch (\Throwable $e) {
            // ئەگەر دەسەڵاتەکە هێشتا دروست نەکرابوو
        }

        if ($user->can('pos.access') || $user->hasRole('cashier')) {
            return redirect()->route('pos.index');
        }

        abort(403, 'دەسەڵاتی پێویستت نییە بۆ دەستپێگەیشتن بەم بەشە.');
    }
}
