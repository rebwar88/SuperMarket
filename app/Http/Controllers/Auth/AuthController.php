<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domains\Auth\Models\User;
use App\Domains\System\Models\SystemNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('cashier') || (!$user->hasRole('super_admin') && !$user->can('dashboard.view'))) {
                return redirect()->route('pos.index');
            }
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required'    => 'تکایە ئیمەیڵ یان ناوی بەکارهێنەر بنووسە.',
            'password.required' => 'تکایە وشەی نهێنی بنووسە.',
        ]);

        $loginInput = trim($credentials['login']);
        $password = $credentials['password'];

        $user = User::where('email', $loginInput)
            ->orWhere('username', $loginInput)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            
            // ئەگەر کاشێر بوو و پێشتر کراوە بووبێت، ئاگادارییەکی هەستیار بۆ ئەدمین دەنێرێت
            if ($user->hasRole('cashier') || (!$user->hasRole('super_admin') && !$user->hasRole('admin'))) {
                if (Schema::hasTable('sessions')) {
                    $activeSessionsCount = DB::table('sessions')->where('user_id', $user->id)->count();
                    if ($activeSessionsCount > 0) {
                        SystemNotification::create([
                            'type' => 'security',
                            'title' => 'ئاگاداریی ئاسایشی هەستیار: گۆڕینی ئامێر',
                            'message' => "کاشێر [ {$user->name} ] لە کۆمپیوتەرێکی نوێوە لۆگین بوو و کۆمپیوتەری پێشووی خۆکارانە فڕێ درایە دەرەوە.",
                            'severity' => 'danger',
                        ]);
                        DB::table('sessions')->where('user_id', $user->id)->delete();
                    }
                }

                // تۆمارکردنی ئاگاداری چوونەژوورەوەی کاشێر
                SystemNotification::create([
                    'type' => 'login',
                    'title' => 'چوونەژوورەوەی کاشێر بۆ سندوق',
                    'message' => "کاشێر [ {$user->name} ] لە کاتژمێر " . now()->format('H:i') . " دەستی بە کارکردن کرد.",
                    'severity' => 'info',
                ]);
            }

            Auth::login($user, (bool) $request->filled('remember'));
            $request->session()->regenerate();

            if ($user->hasRole('cashier') || (!$user->hasRole('super_admin') && !$user->can('dashboard.view'))) {
                return redirect()->intended(route('pos.index'));
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'login' => 'ناوی بەکارهێنەر/ئیمەیڵ یان وشەی نهێنی هەڵەیە.',
        ])->onlyInput('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user && ($user->hasRole('cashier') || !$user->hasRole('super_admin'))) {
            SystemNotification::create([
                'type' => 'logout',
                'title' => 'دەرچوونی کاشێر لە سندوق',
                'message' => "کاشێر [ {$user->name} ] لە کاتژمێر " . now()->format('H:i') . " لە سیستەم هاتە دەرەوە.",
                'severity' => 'warning',
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
