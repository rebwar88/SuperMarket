<?php
declare(strict_types=1);
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RequireManagerPin
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return $next($request);
        }
        
        $pin = $request->header('X-Manager-Pin') ?? $request->input('manager_pin');
        if (!$pin) {
            return response()->json(['success' => false, 'message' => 'بۆ ئەم کارە پاسووردی بەڕێوەبەر (Manager PIN) پێویستە.'], 403);
        }
        
        $userClass = config('auth.providers.users.model');
        $adminWithPin = $userClass::role('admin')->get()->first(function($admin) use ($pin) {
            return Hash::check($pin, $admin->password) || $pin === ($admin->pin ?? '');
        });

        if (!$adminWithPin) {
            return response()->json(['success' => false, 'message' => 'پاسووردی بەڕێوەبەر هەڵەیە.'], 403);
        }
        return $next($request);
    }
}