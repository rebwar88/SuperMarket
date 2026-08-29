<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDynamicPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->can($permission)) {
            return $next($request);
        }

        abort(403, 'دەسەڵاتی پێویستت نییە بۆ دەستپێگەیشتن بەم بەشە.');
    }
}
