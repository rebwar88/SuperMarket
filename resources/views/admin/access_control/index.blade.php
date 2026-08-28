<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بەڕێوەبردنی دەسەڵاتەکان و کارمەندان - RBAC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> * { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">

    @php
        $permNamesKurdish = [
            'dashboard.view'     => 'داشبۆرد و داتای دارایی',
            'pos.access'         => 'سندوق (POS)',
            'inventory.manage'   => 'بەڕێوەبردنی کۆگا',
            'inventory.purchase' => 'تۆمارکردنی کڕین',
            'debts.manage'       => 'دەفتەری قەرز',
            'expenses.manage'    => 'تۆمارکردنی خەرجی',
            'promotions.manage'  => 'ئۆفەر و داشکاندن',
            'settings.manage'    => 'ڕێکخستنەکان',
        ];
    @endphp

    <!-- سەرپەڕە -->
    <header class="bg-slate-950/80 border-b border-slate-800 px-6 py-3.5 flex items-center justify-between sticky top-0 z-40 backdrop-blur">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-xl text-slate-950 shadow-lg shadow-emerald-500/20">S</div>
            <div>
                <h1 class="font-extrabold text-base text-white">بەڕێوەبردنی دەسەڵاتەکان و کارمەندان</h1>
                <p class="text-xs text-slate-400">دەستکاریکردن و بەڕێوەبردنی ڕۆڵ، مۆڵەت و بەکارهێنەران</p>
            </div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="bg-slate-800 text-slate-300 text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-700 transition">داشبۆرد</a>
    </header>

    <main class="flex-1 p-6 max-w-7xl w-full mx-auto space-y-6">

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-xl text-sm font-semibold">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="flex gap-3">
            <button onclick="document.getElementById('modal-add-role').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>دروستکردنی ڕۆڵی نوێ</span>
            </button>
            <button onclick="document.getElementById('modal-add-user').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>زیادکردنی کارمەند</span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- خشتەی کارمەندان -->
            <div class="lg:col-span-7 bg-slate-800/80 border border-slate-700/80 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-700/80 bg-slate-800 flex justify-between items-center">
                    <h2 class="font-bold text-sm text-white">کارمەندانی تۆمارکراو</h2>
                    <span class="text-xs text-slate-400 font-mono">{{ $users->count() }} هەژمار</span>
                </div>
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-900/60 text-slate-400 font-bold border-b border-slate-700/50">
                        <tr>
                            <th class="p-3.5">ناوی کارمەند</th>
                            <th class="p-3.5">ناوی بەکارهێنەر / ئیمەیڵ</th>
                            <th class="p-3.5">ڕۆڵی پێدراو</th>
                            <th class="p-3.5 text-center">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/40 font-medium">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-700/30">
                                <td class="p-3.5 font-bold text-white">{{ $u->name }}</td>
                                <td class="p-3.5 font-mono text-slate-300">
                                    <span class="text-emerald-400 font-semibold">{{ $u->username ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-500 block">{{ $u->email }}</span>
                                </td>
                                <td class="p-3.5">
                                    @forelse($u->roles as $r)
                                        <span class="bg-slate-700 text-emerald-400 border border-slate-600 px-2.5 py-1 rounded-lg text-[11px] font-bold">
                                            {{ $r->name === 'super_admin' ? 'بەڕێوەبەری گشتی' : ($r->name === 'cashier' ? 'کاشێر' : $r->name) }}
                                        </span>
                                    @empty
                                        <span class="text-slate-500 text-[11px]">بێ ڕۆڵ</span>
                                    @endforelse
                                </td>
                                <td class="p-3.5 text-center">
                                    <button onclick='openEditUserModal(@json($u), @json($u->roles->pluck("name")))' class="bg-slate-700 hover:bg-slate-600 text-slate-200 px-2.5 py-1 rounded-lg text-[11px] font-bold transition">
                                        دەستکاری
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ڕۆڵەکان و دەسەڵاتەکانیان -->
            <div class="lg:col-span-5 bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 shadow-sm space-y-4">
                <h2 class="font-bold text-sm text-white border-b border-slate-700 pb-3">ڕۆڵە ناسێنراوەکان</h2>
                <div class="space-y-3">
                    @foreach($roles as $r)
                        <div class="p-3.5 bg-slate-900/60 border border-slate-700/60 rounded-xl space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-white text-xs">
                                    {{ $r->name === 'super_admin' ? 'بەڕێوەبەری گشتی' : ($r->name === 'cashier' ? 'کاشێر' : $r->name) }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $r->users->count() }} کارمەند</span>
                                    @if($r->name !== 'super_admin')
                                        <button onclick='openEditRoleModal(@json($r), @json($r->permissions->pluck("name")))' class="text-[11px] text-indigo-400 hover:text-indigo-300 font-bold underline">
                                            دەستکاری
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @if($r->name === 'super_admin')
                                    <span class="bg-rose-500/20 text-rose-300 text-[11px] px-2.5 py-1 rounded font-bold border border-rose-500/30">سەرجەم دەسەڵاتەکان (Full Access)</span>
                                @else
                                    @forelse($r->permissions as $p)
                                        <span class="bg-slate-800 text-slate-200 text-[11px] px-2.5 py-1 rounded-lg border border-slate-700 flex items-center gap-1 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            <span>{{ $permNamesKurdish[$p->name] ?? $p->name }}</span>
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-slate-500">هیچ دەسەڵاتێکی نییە</span>
                                    @endforelse
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </main>

    <!-- مۆداڵی زیادکردنی ڕۆڵ -->
    <div id="modal-add-role" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-lg w-full rounded-2xl shadow-2xl p-5 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b border-slate-700 pb-3">
                <h3 class="font-bold text-sm text-white">دروستکردنی ڕۆڵی نوێ</h3>
                <button onclick="document.getElementById('modal-add-role').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>
            
            <form action="{{ route('admin.access.role.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی ڕۆڵ:</label>
                    <input type="text" name="name" required placeholder="سەرپەرشتیاری کۆگا" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-2">دەسەڵاتە ڕێگەپێدراوەکان دیاری بکە:</label>
                    <div class="space-y-3 bg-slate-900/80 p-3.5 rounded-xl border border-slate-700/80">
                        @foreach($permissions as $group => $perms)
                            <div class="border-b border-slate-800 pb-2.5 last:border-b-0 last:pb-0">
                                <h4 class="font-bold text-emerald-400 text-xs mb-2 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>{{ $group }}</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($perms as $p)
                                        <label class="flex items-center gap-2.5 text-slate-200 cursor-pointer bg-slate-800/60 hover:bg-slate-800 p-2 rounded-lg border border-slate-700/50 transition">
                                            <input type="checkbox" name="permissions[]" value="{{ $p->name }}" class="w-4 h-4 rounded bg-slate-900 border-slate-600 text-emerald-500">
                                            <span class="text-[11px] font-medium">{{ $p->display_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-xl transition">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-role').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-2.5 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مۆداڵی دەستکاریکردنی ڕۆڵ (Edit Role) -->
    <div id="modal-edit-role" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-lg w-full rounded-2xl shadow-2xl p-5 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b border-slate-700 pb-3">
                <h3 class="font-bold text-sm text-white">دەستکاریکردنی ڕۆڵ و دەسەڵاتەکان</h3>
                <button onclick="document.getElementById('modal-edit-role').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>
            
            <form id="form-edit-role" action="" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی ڕۆڵ:</label>
                    <input type="text" id="edit-role-name" name="name" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-2">دەسەڵاتەکان:</label>
                    <div class="space-y-3 bg-slate-900/80 p-3.5 rounded-xl border border-slate-700/80">
                        @foreach($permissions as $group => $perms)
                            <div class="border-b border-slate-800 pb-2.5 last:border-b-0 last:pb-0">
                                <h4 class="font-bold text-emerald-400 text-xs mb-2 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>{{ $group }}</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($perms as $p)
                                        <label class="flex items-center gap-2.5 text-slate-200 cursor-pointer bg-slate-800/60 hover:bg-slate-800 p-2 rounded-lg border border-slate-700/50 transition">
                                            <input type="checkbox" name="permissions[]" value="{{ $p->name }}" class="edit-role-perm-cb w-4 h-4 rounded bg-slate-900 border-slate-600 text-emerald-500">
                                            <span class="text-[11px] font-medium">{{ $p->display_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-xl transition">نوێکردنەوەی ڕۆڵ</button>
                    <button type="button" onclick="document.getElementById('modal-edit-role').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-2.5 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مۆداڵی زیادکردنی کارمەند -->
    <div id="modal-add-user" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-md w-full rounded-2xl shadow-2xl p-5 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-700 pb-3">
                <h3 class="font-bold text-sm text-white">زیادکردنی کارمەندی نوێ</h3>
                <button onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>
            
            <form action="{{ route('admin.access.user.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی کارمەند:</label>
                    <input type="text" name="name" required placeholder="وەک: میلاد" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ناوی بەکارهێنەر (Username):</label>
                        <input type="text" name="username" placeholder="milad" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ئیمەیڵ:</label>
                        <input type="email" name="email" required placeholder="milad@market.com" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">وشەی نهێنی:</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">دووبارەکردنەوەی وشەی نهێنی:</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ڕۆڵ دیاری بکە:</label>
                    <select name="role" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name === 'super_admin' ? 'بەڕێوەبەری گشتی' : ($r->name === 'cashier' ? 'کاشێر' : $r->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl transition">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-2.5 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مۆداڵی دەستکاریکردنی کارمەند (Edit User) -->
    <div id="modal-edit-user" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-md w-full rounded-2xl shadow-2xl p-5 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-700 pb-3">
                <h3 class="font-bold text-sm text-white">دەستکاریکردنی زانیارییەکانی کارمەند</h3>
                <button onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>
            
            <form id="form-edit-user" action="" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی کارمەند:</label>
                    <input type="text" id="edit-user-name" name="name" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ناوی بەکارهێنەر (Username):</label>
                        <input type="text" id="edit-user-username" name="username" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ئیمەیڵ:</label>
                        <input type="email" id="edit-user-email" name="email" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">وشەی نهێنی نوێ (ئارەزوومەندانە):</label>
                        <input type="password" name="password" placeholder="ئەگەر ناگۆڕدرێت بەتاڵی بهێڵەرەوە" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">دووبارەکردنەوەی وشەی نهێنی:</label>
                        <input type="password" name="password_confirmation" placeholder="دووبارەکردنەوە" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ڕۆڵ دیاری بکە:</label>
                    <select id="edit-user-role" name="role" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none focus:border-emerald-500">
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name === 'super_admin' ? 'بەڕێوەبەری گشتی' : ($r->name === 'cashier' ? 'کاشێر' : $r->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl transition">پاشەکەوتکردنی گۆڕانکارییەکان</button>
                    <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-2.5 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- سکریپتی کارپێکردنی مۆداڵەکانی دەستکاری -->
    <script>
        function openEditRoleModal(role, currentPerms) {
            document.getElementById('form-edit-role').action = '/access-control/roles/' + role.id;
            document.getElementById('edit-role-name').value = role.name;
            
            // دیاریکردنی چێکبۆکسەکان
            document.querySelectorAll('.edit-role-perm-cb').forEach(cb => {
                cb.checked = currentPerms.includes(cb.value);
            });

            document.getElementById('modal-edit-role').classList.remove('hidden');
        }

        function openEditUserModal(user, currentRoles) {
            document.getElementById('form-edit-user').action = '/access-control/users/' + user.id;
            document.getElementById('edit-user-name').value = user.name;
            document.getElementById('edit-user-username').value = user.username || '';
            document.getElementById('edit-user-email').value = user.email;
            
            if (currentRoles.length > 0) {
                document.getElementById('edit-user-role').value = currentRoles[0];
            }

            document.getElementById('modal-edit-user').classList.remove('hidden');
        }
    </script>

</body>
</html>
