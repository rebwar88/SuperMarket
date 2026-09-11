<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerifyShiftOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasActiveShift = DB::table('register_shifts')
            ->where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->exists();

        if (!$hasActiveShift) {
            $message = 'هیچ شیفتێکی کراوە بوونی نییە. تکایە سەرەتا شیفتێک بکەرەوە.';
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 403);
            }
            return redirect()->route('pos.index')->with('error', $message);
        }

        return $next($request);
    }
}