<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        return $this->showLogin();
    }

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

            $settingsRaw = DB::table('store_settings')->pluck('value', 'key')->toArray(); 
                   $defaults = [
            'market_name' => 'سوپەرمارکێتی میلاد',
            'market_slogan' => 'سیستەمی بەڕێوەبردن و فرۆشتنی پێشکەوتوو',
            'market_logo' => '',
            'currency_symbol' => 'د.ع',
        ];
        $settings = array_merge($defaults, $settingsRaw);

        return view('auth.login', compact('settings'));
    }

    public function login(Request $request): RedirectResponse
    {
        // وەرگرتنی ناسنامەی بەکارهێنەر لە ژێر هەر ناوێک بێت لە فۆڕمەکەوە
        $loginInput = $request->input('username') ?? $request->input('email') ?? $request->input('login');
        $password = $request->input('password');

        if (empty($loginInput) || empty($password)) {
            return back()->withErrors([
                'username' => 'تکایە ناوی بەکارهێنەر و وشەی نهێنی بنووسە.',
            ])->withInput();
        }

        $remember = $request->boolean('remember');
        Session::forget('url.intended');

        // هەوڵدان بۆ چوونەژوورەوە سەرەتا بە username پاشان بە email
        $isLoggedIn = Auth::attempt(['username' => $loginInput, 'password' => $password], $remember)
            || Auth::attempt(['email' => $loginInput, 'password' => $password], $remember);

        if ($isLoggedIn) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (isset($user->is_active) && !$user->is_active) {
                Auth::logout();
                return back()->withErrors(['username' => 'ئەم هەژمارە ناچالاک کراوە.']);
            }

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'username' => 'ناوی بەکارهێنەر یان وشەی نهێنی هەڵەیە.',
        ])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectBasedOnRole($user): RedirectResponse
    {
        $role = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_uuid', $user->id)
            ->value('roles.name');

        if (strtolower((string) $role) === 'cashier') {
            return redirect()->to('/pos');
        }

        return redirect()->to('/dashboard');
    }
}
