<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccessControlController extends Controller
{
    private array $kurdishRoles = [
        'admin' => 'بەڕێوەبەری گشتی (Admin)',
        'cashier' => 'کاشێر (سندوق و وەسڵ)',
        'stock_manager' => 'بەڕێوەبەری کۆگا و کاڵاکان',
        'accountant' => 'ژمێریار و حیسابات',
        'supervisor' => 'سەرپەرشتیاری ستاف',
    ];

    private array $moduleTranslations = [
        'products' => 'کاڵاکان و نرخ',
        'categories' => 'گرووپ و جۆرەکان',
        'orders' => 'فرۆشتن و پسوولەکان',
        'order_items' => 'وردەکاری فرۆشتن',
        'expenses' => 'خەرجییەکان',
        'expense_categories' => 'جۆری خەرجی',
        'customers' => 'موشتەرییەکان',
        'suppliers' => 'دابینکەران',
        'users' => 'کارمەندان و هەژمارەکان',
        'roles' => 'ڕۆڵ و دەسەڵاتەکان',
        'settings' => 'ڕێکخستنە گشتییەکان',
        'promotions' => 'ئۆفەر و داشکاندن',
        'reports' => 'ڕاپۆرت و شیکاری',
        'shifts' => 'شیفت و سندوق',
        'registers' => 'ئامێرەکانی کاشێر',
        'batches' => 'باچ و بەسەرچوون',
        'warehouses' => 'کۆگاکان',
        'parties' => 'قەرزدارەکان و پارەدان',
    ];

    public function index(): View
    {
        $this->syncDynamicPermissions();

        $settingsRaw = DB::table('store_settings')->pluck('value', 'key')->toArray();
        $defaults = [
            'market_name' => 'سوپەرمارکێتی میلاد',
            'market_logo' => '',
            'currency_symbol' => 'د.ع',
        ];
        $settings = array_merge($defaults, $settingsRaw);

        // هێنانی بەکارهێنەران
        $users = DB::table('users')
            ->leftJoin('model_has_roles', function($join) {
                $join->on('model_has_roles.model_uuid', '=', 'users.id')
                     ->orOn('model_has_roles.model_uuid', '=', DB::raw('CAST(users.id AS CHAR)'));
            })
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('users.*', 'roles.name as role_name', 'roles.id as role_id')
            ->orderByDesc('users.created_at')
            ->get();

        // هێنانی ڕۆڵەکان لەگەڵ لیستی IDـی مۆڵەتەکانیان
        $rolesRaw = DB::table('roles')->get();
        $roles = $rolesRaw->map(function ($r) {
            $r->permission_ids = DB::table('role_has_permissions')
                ->where('role_id', $r->id)
                ->pluck('permission_id')
                ->toArray();
            $r->permissions_count = count($r->permission_ids);
            return $r;
        });

        // گروپکردنی مۆڵەتەکان بەپێی بەش (Module)
        $allPermissions = DB::table('permissions')->get();
        $groupedPermissions = [];
        foreach ($allPermissions as $perm) {
            $parts = explode('.', $perm->name);
            $module = $parts[0] ?? 'other';
            $groupedPermissions[$module][] = $perm;
        }

        $kurdishRoles = $this->kurdishRoles;
        $moduleTranslations = $this->moduleTranslations;

        return view('admin.access.index', compact(
            'users',
            'roles',
            'groupedPermissions',
            'settings',
            'kurdishRoles',
            'moduleTranslations'
        ));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:4'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $userId = (string) Str::uuid();

        DB::table('users')->insert([
            'id' => $userId,
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => (int) $validated['role_id'],
            'model_type' => 'App\\Models\\User',
            'model_uuid' => $userId,
        ]);

        return redirect()->route('admin.access.index')->with('success', 'کارمەندی نوێ بە سەرکەوتوویی تۆمارکرا.');
    }

    public function updateUser(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $id . ',id'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $id . ',id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:4'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'updated_at' => now(),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        DB::table('users')->where('id', $id)->update($updateData);

        DB::table('model_has_roles')->where('model_uuid', $id)->delete();
        DB::table('model_has_roles')->insert([
            'role_id' => (int) $validated['role_id'],
            'model_type' => 'App\\Models\\User',
            'model_uuid' => $id,
        ]);

        return redirect()->route('admin.access.index')->with('success', 'زانیاری و ڕۆڵی کارمەندەکە بە سەرکەوتوویی گۆڕدرا.');
    }

    public function deleteUser(string $id): RedirectResponse
    {
        DB::table('model_has_roles')->where('model_uuid', $id)->delete();
        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('admin.access.index')->with('success', 'کارمەندەکە سڕایەوە.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
        ]);

        $roleId = DB::table('roles')->insertGetId([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!empty($validated['permissions'])) {
            $perms = array_map(fn($pid) => [
                'permission_id' => (int) $pid,
                'role_id' => (int) $roleId,
            ], $validated['permissions']);
            DB::table('role_has_permissions')->insert($perms);
        }

        return redirect()->route('admin.access.index')->with('success', 'ڕۆڵی نوێ دروستکرا.');
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $id . ',id'],
            'permissions' => ['nullable', 'array'],
        ]);

        DB::table('roles')->where('id', $id)->update([
            'name' => $validated['name'],
            'updated_at' => now(),
        ]);

        DB::table('role_has_permissions')->where('role_id', $id)->delete();

        if (!empty($validated['permissions'])) {
            $perms = array_map(fn($pid) => [
                'permission_id' => (int) $pid,
                'role_id' => $id,
            ], $validated['permissions']);
            DB::table('role_has_permissions')->insert($perms);
        }

        return redirect()->route('admin.access.index')->with('success', 'دەسەڵات و توانستەکانی ڕۆڵەکە نوێکرانەوە.');
    }

    public function deleteRole(int $id): RedirectResponse
    {
        $assigned = DB::table('model_has_roles')->where('role_id', $id)->count();
        if ($assigned > 0) {
            return redirect()->route('admin.access.index')->with('error', 'ناتوانیت ئەم ڕۆڵە بسڕیتەوە چونکە کارمەند هەیە لەسەری.');
        }

        DB::table('role_has_permissions')->where('role_id', $id)->delete();
        DB::table('roles')->where('id', $id)->delete();

        return redirect()->route('admin.access.index')->with('success', 'ڕۆڵەکە سڕایەوە.');
    }

    public function toggleUserStatus(string $id): RedirectResponse
    {
        $user = DB::table('users')->where('id', $id)->first();
        if ($user) {
            DB::table('users')->where('id', $id)->update([
                'is_active' => $user->is_active ? 0 : 1,
                'updated_at' => now(),
            ]);
        }
        return redirect()->route('admin.access.index')->with('success', 'دۆخی کارمەندەکە گۆڕدرا.');
    }

    /**
     * سکانکردنی داینامیکی مۆدێلەکان و داتابەیس بۆ دروستکردنی خۆکارانەی Abilities/Permissions
     */
    private function syncDynamicPermissions(): void
    {
        $actions = ['view', 'create', 'edit', 'delete'];
        $databaseName = DB::getDatabaseName();
        $tables = DB::select("SELECT TABLE_NAME as name FROM information_schema.tables WHERE table_schema = ? AND table_type = 'BASE TABLE'", [$databaseName]);

        $existingPerms = DB::table('permissions')->pluck('name')->toArray();
        $newPerms = [];

        foreach ($tables as $t) {
            $moduleName = strtolower($t->name);
            foreach ($actions as $act) {
                $permName = "{$moduleName}.{$act}";
                if (!in_array($permName, $existingPerms, true)) {
                    $newPerms[] = [
                        'name' => $permName,
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($newPerms)) {
            DB::table('permissions')->insert($newPerms);
        }
    }
}
