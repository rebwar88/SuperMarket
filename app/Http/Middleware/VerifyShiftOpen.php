<?php
declare(strict_types=1);
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VerifyShiftOpen
{
    public function handle(Request $request, Closure $next)
    {
        $hasOpenShift = DB::table('register_shifts')
            ->where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->exists();

        if (!$hasOpenShift) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'هیچ شیفتێکی کراوە بوونی نییە. تکایە سەرەتا شیفتێک بکەرەوە.'], 403);
            }
            abort(403, 'هیچ شیفتێکی کراوە بوونی نییە. تکایە سەرەتا شیفتێک بکەرەوە.');
        }
        return $next($request);
    }
}