<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Auth\Models\Permission;
use App\Domains\Auth\Models\Role;
use App\Domains\Auth\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccessControlController extends Controller
{
    private array $permissionMeta = [
        'dashboard.view'     => ['title' => 'بینینی داشبۆرد و داتای دارایی', 'group' => 'داشبۆرد و ئامار'],
        'pos.access'         => ['title' => 'فرۆشتن لە سندوق (POS)', 'group' => 'سندوق و فرۆشتن'],
        'inventory.manage'   => ['title' => 'بەڕێوەبردنی کۆگا و کاڵاکان', 'group' => 'کۆگا و کاڵاکان'],
        'inventory.purchase' => ['title' => 'تۆمارکردنی پسوولەی کڕین', 'group' => 'کۆگا و کاڵاکان'],
        'debts.manage'       => ['title' => 'دەفتەری قەرزی کڕیار و دابینکەر', 'group' => 'قەرز و حیسابات'],
        'expenses.manage'    => ['title' => 'تۆمارکردنی خەرجییەکان', 'group' => 'ژمێریاری و دارایی'],
        'promotions.manage'  => ['title' => 'بەڕێوەبردنی ئۆفەر و داشکاندن', 'group' => 'سندوق و فرۆشتن'],
        'settings.manage'    => ['title' => 'ڕێکخستنەکان و کارمەندان', 'group' => 'ڕێکخستنەکانی سیستەم'],
    ];

    public function index(): View
    {
        $this->syncPermissions();

        $users = User::with('roles')->latest()->get();
        $roles = Role::with('permissions')->get();
        
        $permissions = Permission::all()->map(function ($perm) {
            $meta = $this->permissionMeta[$perm->name] ?? [
                'title' => $perm->name,
                'group' => 'بەشەکانی تر'
            ];
            $perm->display_name = $meta['title'];
            $perm->group_name = $meta['group'];
            return $perm;
        })->groupBy('group_name');

        return view('admin.access_control.index', compact('users', 'roles', 'permissions'));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
        ], [
            'name.unique' => 'ئەم ناوەی ڕۆڵ پێشتر هەیە.',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.access.index')->with('success', 'ڕۆڵی نوێ بە سەرکەوتوویی دروستکرا.');
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
        ]);

        if ($role->name !== 'super_admin') {
            $role->update(['name' => $validated['name']]);
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        return redirect()->route('admin.access.index')->with('success', 'ڕۆڵەکە و دەسەڵاتەکانی بە سەرکەوتوویی نوێکرانەوە.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [
            'email.unique' => 'ئەم ئیمەیڵە پێشتر تۆمارکراوە.',
            'username.unique' => 'ئەم ناوی بەکارهێنەرە پێشتر بەکارهاتووە.',
            'password.min' => 'وشەی نهێنی نابێت لە ٦ پیت کەمتر بێت.',
            'password.confirmed' => 'دووبارەکردنەوەی وشەی نهێنی وەک یەک نییە.',
        ]);

        $username = !empty($validated['username']) 
            ? $validated['username'] 
            : explode('@', $validated['email'])[0];

        if (User::where('username', $username)->exists()) {
            $username .= '_' . Str::random(3);
        }

        $user = User::create([
            'name' => $validated['name'],
            'username' => $username,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.access.index')->with('success', 'کارمەند بە سەرکەوتوویی زیادکرا.');
    }

    public function updateUser(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username,' . $user->id],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [
            'email.unique' => 'ئەم ئیمەیڵە پێشتر بەکارهاتووە.',
            'username.unique' => 'ئەم ناوی بەکارهێنەرە پێشتر بەکارهاتووە.',
            'password.confirmed' => 'دووبارەکردنەوەی وشەی نهێنی وەک یەک نییە.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'] ?: $user->username,
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.access.index')->with('success', 'زانیارییەکانی کارمەند بە سەرکەوتوویی نوێکرانەوە.');
    }

    private function syncPermissions(): void
    {
        foreach (array_keys($this->permissionMeta) as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        if (!$cashierRole->hasPermissionTo('pos.access')) {
            $cashierRole->givePermissionTo('pos.access');
        }

        $firstUser = User::first();
        if ($firstUser && $firstUser->roles()->count() === 0) {
            $firstUser->assignRole($adminRole);
        }
    }
}
