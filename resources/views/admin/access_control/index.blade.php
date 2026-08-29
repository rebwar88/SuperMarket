@extends('layouts.admin')

@section('title', 'دەسەڵات و کارمەندان')

@section('content')
    @if(session('success'))
        <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 p-4 rounded-2xl text-xs font-bold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-extrabold text-base text-white">بەڕێوەبردنی دەسەڵات و کارمەندان (RBAC)</h2>
            <p class="text-xs text-slate-400">دیاریکردنی ڕۆڵ (Admin / Cashier / Stock Manager) و مۆڵەتەکانی بەکارهێنەران</p>
        </div>
        <button onclick="document.getElementById('modal-add-user').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-indigo-600/20 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            <span>زیادکردنی کارمەندی نوێ</span>
        </button>
    </div>

    <!-- خشتەی بەکارهێنەران -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-900">
            <h3 class="font-bold text-sm text-white">لیستی کارمەندان و ڕۆڵەکانیان</h3>
            <span class="text-xs text-slate-400 font-mono">کۆی کارمەندان: {{ isset($users) ? (is_countable($users) ? count($users) : $users->count()) : 0 }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950/60 text-slate-400 font-bold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">ناوی کارمەند</th>
                        <th class="p-3.5">ئیمەیڵ</th>
                        <th class="p-3.5">ڕۆڵ (Role)</th>
                        <th class="p-3.5">بەرواری دروستبوون</th>
                        <th class="p-3.5 text-center">دۆخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse ($users ?? [] as $user)
                        @php
                            $userRole = 'کاشێر';
                            if (isset($user->role) && !empty($user->role)) {
                                $userRole = $user->role;
                            } elseif (is_object($user) && method_exists($user, 'roles') && $user->roles && $user->roles->first()) {
                                $userRole = $user->roles->first()->name ?? 'کاشێر';
                            }
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-3.5 font-bold text-white">{{ $user->name }}</td>
                            <td class="p-3.5 font-mono text-slate-300">{{ $user->email }}</td>
                            <td class="p-3.5">
                                <span class="bg-indigo-950/60 text-indigo-300 px-2.5 py-1 rounded-lg text-xs font-bold border border-indigo-500/30">
                                    {{ $userRole }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono text-slate-400">{{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('Y-m-d') : '-' }}</td>
                            <td class="p-3.5 text-center">
                                <span class="text-emerald-400 font-bold bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20">چالاکە</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">هیچ کارمەندێک نەدۆزرایەوە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- مۆداڵی زیادکردنی کارمەند -->
    <div id="modal-add-user" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">تۆمارکردنی کارمەندی نوێ</h3>
                <button onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.access.store') ?? '#' }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی تەواو:</label>
                    <input type="text" name="name" required placeholder="وەک: محەمەد ئەحمەد" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ئیمەیڵ (بۆ چوونەژوورەوە):</label>
                    <input type="email" name="email" required placeholder="cashier1@market.com" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">وشەی نهێنی (Password):</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ڕۆڵ و دەسەڵات:</label>
                    <select name="role" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none focus:border-indigo-500">
                        <option value="cashier">کاشێر (تەنها سندوق و وەسڵ)</option>
                        <option value="stock_manager">بەڕێوەبەری کۆگا (تەنها ستۆک و کڕین)</option>
                        <option value="admin">ئەدمین (سەرجەم دەسەڵاتەکان)</option>
                    </select>
                </div>
                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>
@endsection
