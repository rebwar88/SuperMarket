@extends('layouts.admin')

@section('title', 'دەسەڵات و کارمەندان')

@section('content')
    @if(session('success'))
        <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 p-4 rounded-2xl text-xs font-bold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-950/80 border border-rose-500/40 text-rose-300 p-4 rounded-2xl text-xs font-bold flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-950/80 border border-rose-500/40 text-rose-300 p-4 rounded-2xl text-xs font-bold space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- سەرپەڕە -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-extrabold text-base text-white">بەڕێوەبردنی کارمەندان و توانستەکانی ڕۆڵ (Dynamic RBAC)</h2>
            <p class="text-xs text-slate-400">دیاریکردنی ڕۆڵی کارمەند، دەستکاریکردنی توانستە داینامیکییەکان بۆ سەرجەم بەشەکان</p>
        </div>
        <div class="flex gap-2">
            <button onclick="document.getElementById('modal-add-user').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-indigo-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>زیادکردنی کارمەند</span>
            </button>
            <button onclick="document.getElementById('modal-add-role').classList.remove('hidden')" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold px-4 py-2.5 rounded-xl transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>دروستکردنی ڕۆڵی نوێ</span>
            </button>
        </div>
    </div>

    <!-- گریدێک بۆ خشتەی کارمەندان و ڕۆڵەکان -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- خشتەی کارمەندان -->
        <div class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-900">
                <h3 class="font-bold text-sm text-white">لیستی سەرجەم کارمەندان</h3>
                <span class="text-xs text-slate-400 font-mono">{{ count($users) }} کارمەند</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-950/60 text-slate-400 font-bold border-b border-slate-800">
                        <tr>
                            <th class="p-3.5">ناوی تەواو</th>
                            <th class="p-3.5">بەکارهێنەر</th>
                            <th class="p-3.5">ڕۆڵی ئێستا</th>
                            <th class="p-3.5 text-center">دۆخ</th>
                            <th class="p-3.5 text-left">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @forelse ($users as $u)
                            @php
                                $rk = strtolower((string)($u->role_name ?? ''));
                                $displayRole = $kurdishRoles[$rk] ?? ($u->role_name ?? 'بێ ڕۆڵ');
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-3.5 font-bold text-white">
                                    <div>{{ $u->name }}</div>
                                    @if(!empty($u->email))
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $u->email }}</div>
                                    @endif
                                </td>
                                <td class="p-3.5 font-mono text-indigo-400 font-bold">{{ $u->username }}</td>
                                <td class="p-3.5">
                                    <span class="bg-indigo-950/60 text-indigo-300 px-2.5 py-1 rounded-lg text-xs font-bold border border-indigo-500/30">
                                        {{ $displayRole }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-center">
                                    <form action="{{ route('admin.access.users.toggle', $u->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2 py-0.5 rounded text-[11px] font-bold transition {{ $u->is_active ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                                            {{ $u->is_active ? 'چالاکە' : 'ناچالاکە' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="p-3.5 text-left">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button onclick="openEditUserModal({{ json_encode($u) }})" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-amber-400 rounded-lg border border-slate-700 transition" title="گۆڕینی ڕۆڵ و زانیاری">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ route('admin.access.users.delete', $u->id) }}" method="POST" onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم کارمەندە؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-slate-800 hover:bg-rose-950/80 text-rose-400 rounded-lg border border-slate-700 transition" title="سڕینەوە">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">هیچ کارمەندێک نییە.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- خشتەی ڕۆڵەکان و توانستەکان -->
        <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-900">
                <h3 class="font-bold text-sm text-white">ڕۆڵەکان و مۆڵەتی بەشەکان</h3>
                <span class="text-xs text-slate-400 font-mono">{{ count($roles) }} ڕۆڵ</span>
            </div>

            <div class="p-4 space-y-3">
                @forelse($roles as $r)
                    @php
                        $roleKey = strtolower((string)$r->name);
                        $roleTitle = $kurdishRoles[$roleKey] ?? $r->name;
                    @endphp
                    <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl flex items-center justify-between">
                        <div>
                            <div class="font-bold text-xs text-white">{{ $roleTitle }}</div>
                            <div class="text-[11px] text-indigo-400 font-semibold">{{ $r->permissions_count }} توانست چالاکە</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="openEditRoleModal({{ json_encode($r) }})" class="px-2.5 py-1 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 border border-indigo-500/30 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                <span>دەستکاری توانست</span>
                            </button>
                            <form action="{{ route('admin.access.roles.delete', $r->id) }}" method="POST" onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم ڕۆڵە؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-500 hover:text-rose-400 transition" title="سڕینەوە">✕</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">هیچ ڕۆڵێک نەدۆزرایەوە.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- مۆداڵی زیادکردنی کارمەند -->
    <div id="modal-add-user" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">تۆمارکردنی کارمەندی نوێ</h3>
                <button onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.access.users.store') }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی تەواوی کارمەند:</label>
                    <input type="text" name="name" required placeholder="وەک: دانا عەلی" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی بەکارهێنەر (Username بۆ Login):</label>
                    <input type="text" name="username" required placeholder="dana_pos" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ئیمەیڵ (ئیختیاری):</label>
                        <input type="email" name="email" placeholder="dana@market.com" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">تەلەفۆن:</label>
                        <input type="text" name="phone" placeholder="07700000000" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">وشەی نهێنی (Password):</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ڕۆڵ و دەسەڵاتی کارمەند دیاریبکە:</label>
                    <select name="role_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none focus:border-indigo-500">
                        @foreach($roles as $role)
                            @php
                                $rk = strtolower((string)$role->name);
                                $rt = $kurdishRoles[$rk] ?? $role->name;
                            @endphp
                            <option value="{{ $role->id }}">{{ $rt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مۆداڵی دەستکاریکردنی کارمەند و گۆڕینی ڕۆڵ -->
    <div id="modal-edit-user" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">دەستکاریکردنی کارمەند و گۆڕینی ڕۆڵ</h3>
                <button onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form id="form-edit-user" action="" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی تەواو:</label>
                    <input type="text" id="edit-user-name" name="name" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی بەکارهێنەر (Username):</label>
                    <input type="text" id="edit-user-username" name="username" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ئیمەیڵ:</label>
                        <input type="email" id="edit-user-email" name="email" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">تەلەفۆن:</label>
                        <input type="text" id="edit-user-phone" name="phone" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">وشەی نهێنی نوێ (ئەگەر بەتاڵ بێت ناگۆڕدرێت):</label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ڕۆڵی کارمەند (Role):</label>
                    <select id="edit-user-role-id" name="role_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none focus:border-indigo-500">
                        @foreach($roles as $role)
                            @php
                                $rk = strtolower((string)$role->name);
                                $rt = $kurdishRoles[$rk] ?? $role->name;
                            @endphp
                            <option value="{{ $role->id }}">{{ $rt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-500 text-white font-bold py-3 rounded-xl transition">نوێکردنەوەی هەژمار</button>
                    <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مۆداڵی زیادکردنی ڕۆڵ لەگەڵ تەواوی توانستە داینامیکییەکان -->
    <div id="modal-add-role" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-2xl w-full rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">دروستکردنی ڕۆڵی نوێ و دیاریکردنی توانستەکان</h3>
                <button onclick="document.getElementById('modal-add-role').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.access.roles.store') }}" method="POST" class="p-5 space-y-4 text-xs overflow-y-auto">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی ڕۆڵ:</label>
                    <input type="text" name="name" required placeholder="وەک: Supervisor یان Accountant" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-2">توانست و مۆڵەتە داینامیکییەکان بەپێی بەشەکان:</label>
                    <div class="space-y-3">
                        @foreach($groupedPermissions as $module => $perms)
                            @php
                                $modLabel = $moduleTranslations[$module] ?? strtoupper($module);
                            @endphp
                            <div class="bg-slate-950/80 border border-slate-800/80 rounded-xl p-3">
                                <div class="font-bold text-indigo-400 mb-2 border-b border-slate-800 pb-1 flex justify-between">
                                    <span>{{ $modLabel }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $module }}</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach($perms as $p)
                                        @php
                                            $actionName = explode('.', $p->name)[1] ?? $p->name;
                                            $actionKurdish = match($actionName) {
                                                'view' => 'بینین (View)',
                                                'create' => 'زیادکردن (Add)',
                                                'edit' => 'دەستکاری (Edit)',
                                                'delete' => 'سڕینەوە (Delete)',
                                                default => $actionName
                                            };
                                        @endphp
                                        <label class="flex items-center gap-1.5 p-1 hover:bg-slate-900 rounded cursor-pointer">
                                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="rounded bg-slate-900 border-slate-700 text-indigo-500">
                                            <span class="text-slate-300 text-[11px]">{{ $actionKurdish }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition">دروستکردنی ڕۆڵ</button>
                    <button type="button" onclick="document.getElementById('modal-add-role').classList.add('hidden')" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مۆداڵی دەستکاریکردنی توانستەکانی ڕۆڵ (Edit Role Abilities Modal) -->
    <div id="modal-edit-role" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-2xl w-full rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">دەستکاریکردنی توانست و مۆڵەتەکانی ڕۆڵ</h3>
                <button onclick="document.getElementById('modal-edit-role').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form id="form-edit-role" action="" method="POST" class="p-5 space-y-4 text-xs overflow-y-auto">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی ڕۆڵ:</label>
                    <input type="text" id="edit-role-name" name="name" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-indigo-500">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-slate-300 font-bold">دیاریکردنی مۆڵەتە داینامیکییەکان:</label>
                        <div class="flex gap-2">
                            <button type="button" onclick="selectAllRolePerms(true)" class="text-[10px] text-indigo-400 hover:underline">دیاریکردنی هەمووی</button>
                            <span class="text-slate-600">|</span>
                            <button type="button" onclick="selectAllRolePerms(false)" class="text-[10px] text-rose-400 hover:underline">لابردنی هەمووی</button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($groupedPermissions as $module => $perms)
                            @php
                                $modLabel = $moduleTranslations[$module] ?? strtoupper($module);
                            @endphp
                            <div class="bg-slate-950/80 border border-slate-800/80 rounded-xl p-3">
                                <div class="font-bold text-indigo-400 mb-2 border-b border-slate-800 pb-1 flex justify-between">
                                    <span>{{ $modLabel }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $module }}</span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach($perms as $p)
                                        @php
                                            $actionName = explode('.', $p->name)[1] ?? $p->name;
                                            $actionKurdish = match($actionName) {
                                                'view' => 'بینین',
                                                'create' => 'زیادکردن',
                                                'edit' => 'دەستکاری',
                                                'delete' => 'سڕینەوە',
                                                default => $actionName
                                            };
                                        @endphp
                                        <label class="flex items-center gap-1.5 p-1 hover:bg-slate-900 rounded cursor-pointer">
                                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="role-perm-checkbox rounded bg-slate-900 border-slate-700 text-indigo-500">
                                            <span class="text-slate-300 text-[11px]">{{ $actionKurdish }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition">نوێکردنەوەی دەسەڵاتەکانی ڕۆڵ</button>
                    <button type="button" onclick="document.getElementById('modal-edit-role').classList.add('hidden')" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function openEditUserModal(user) {
        document.getElementById('form-edit-user').action = '/access-control/users/' + user.id;
        document.getElementById('edit-user-name').value = user.name || '';
        document.getElementById('edit-user-username').value = user.username || '';
        document.getElementById('edit-user-email').value = user.email || '';
        document.getElementById('edit-user-phone').value = user.phone || '';
        if (user.role_id) {
            document.getElementById('edit-user-role-id').value = user.role_id;
        }
        document.getElementById('modal-edit-user').classList.remove('hidden');
    }

    function openEditRoleModal(role) {
        document.getElementById('form-edit-role').action = '/access-control/roles/' + role.id;
        document.getElementById('edit-role-name').value = role.name || '';
        
        // ناچالاککردن و پاککردنەوەی سەرجەم چێکبۆکسەکان
        const checkboxes = document.querySelectorAll('.role-perm-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = false;
        });

        // چالاککردنی ئەو مۆڵەتانەی کە ئەم ڕۆڵە هەیەتی
        if (role.permission_ids && Array.isArray(role.permission_ids)) {
            checkboxes.forEach(cb => {
                if (role.permission_ids.includes(parseInt(cb.value))) {
                    cb.checked = true;
                }
            });
        }

        document.getElementById('modal-edit-role').classList.remove('hidden');
    }

    function selectAllRolePerms(check) {
        const checkboxes = document.querySelectorAll('.role-perm-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = check;
        });
    }
</script>
@endsection
