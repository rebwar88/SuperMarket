<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبۆرد - {{ $settings['market_name'] ?? 'سوپەرمارکێت' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> * { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">

    <!-- کۆنتێنەری ئاگادارییە ڕاستەوخۆکان (Live Toast Container) -->
    <div id="toast-container" class="fixed top-5 left-5 z-50 space-y-3 max-w-sm w-full pointer-events-none"></div>

    <!-- سەرپەڕە -->
    <header class="bg-slate-950/90 border-b border-slate-800 px-6 py-3.5 flex items-center justify-between sticky top-0 z-40 backdrop-blur">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-xl text-slate-950 shadow-lg shadow-emerald-500/20">
                S
            </div>
            <div>
                <h1 class="font-extrabold text-base tracking-tight text-white">{{ $settings['market_name'] ?? 'سیستەمی بەڕێوەبردنی سوپەرمارکێت' }}</h1>
                <p class="text-xs text-slate-400">داشبۆردی سەرەکی و کۆنتڕۆڵی گشتی</p>
            </div>
        </div>

        <!-- بەستەری سەرجەم بەشەکان -->
        <nav class="hidden md:flex items-center gap-1.5 text-xs font-bold">
            <a href="{{ route('admin.dashboard') }}" class="bg-slate-800 text-emerald-400 px-3.5 py-2 rounded-xl border border-slate-700/80">داشبۆرد</a>
            <a href="{{ route('admin.inventory.index') }}" class="text-slate-300 hover:bg-slate-800 hover:text-white px-3.5 py-2 rounded-xl transition">کۆگا و کاڵاکان</a>
            <a href="{{ route('admin.debts.index') }}" class="text-slate-300 hover:bg-slate-800 hover:text-white px-3.5 py-2 rounded-xl transition">دەفتەری قەرز</a>
            <a href="{{ route('admin.expenses.index') }}" class="text-slate-300 hover:bg-slate-800 hover:text-white px-3.5 py-2 rounded-xl transition">خەرجییەکان</a>
            <a href="{{ route('admin.promotions.index') }}" class="text-slate-300 hover:bg-slate-800 hover:text-white px-3.5 py-2 rounded-xl transition">ئۆفەر و داشکاندن</a>
            <a href="{{ route('admin.access.index') }}" class="text-indigo-400 hover:bg-indigo-950/40 hover:text-indigo-300 px-3.5 py-2 rounded-xl transition border border-indigo-500/20">دەسەڵات و کارمەندان</a>
            <a href="{{ route('admin.settings.index') }}" class="text-slate-300 hover:bg-slate-800 hover:text-white px-3.5 py-2 rounded-xl transition">ڕێکخستنەکان</a>
        </nav>

        <div class="flex items-center gap-3">
            
            <!-- زەنگۆڵەی ئاگادارییەکان (Notification Center) -->
            <div class="relative">
                <button id="btn-notif" onclick="toggleNotifDropdown()" class="relative bg-slate-800 hover:bg-slate-700 p-2.5 rounded-xl border border-slate-700 transition flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.3886 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span id="notif-badge" class="hidden absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center animate-pulse">0</span>
                </button>

                <!-- مینیۆی ئاگادارییەکان -->
                <div id="notif-dropdown" class="hidden absolute left-0 mt-2 w-80 sm:w-96 bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl z-50 overflow-hidden">
                    <div class="p-3.5 border-b border-slate-700 flex justify-between items-center bg-slate-850">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs text-white">ئاگادارییەکان و چالاکییەکان</span>
                            <span id="notif-count-text" class="text-[10px] bg-slate-700 px-2 py-0.5 rounded text-emerald-400 font-mono font-bold">0 نوێ</span>
                        </div>
                        <button onclick="markAllAsRead()" class="text-[11px] text-indigo-400 hover:underline">هەموو خوێندراوەتەوە</button>
                    </div>
                    <div id="notif-list" class="max-h-80 overflow-y-auto divide-y divide-slate-700/50 text-xs">
                        <div class="p-4 text-center text-slate-500 text-xs">هیچ ئاگادارییەک نییە</div>
                    </div>
                </div>
            </div>

            <a href="{{ route('pos.index') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-lg shadow-emerald-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-40 2 2 0 014 0z"/></svg>
                <span>سندوق (POS)</span>
            </a>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-rose-900/50 hover:text-rose-400 text-slate-300 text-xs font-semibold px-3 py-2.5 rounded-xl border border-slate-700 transition">دەرچوون</button>
            </form>
        </div>
    </header>

    <!-- ناوەڕۆک -->
    <main class="flex-1 p-6 max-w-7xl w-full mx-auto space-y-6">

        <!-- دوگمە خێراکان -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
            <a href="{{ route('pos.index') }}" class="bg-slate-800/80 hover:bg-slate-700 border border-slate-700 p-3.5 rounded-2xl flex items-center gap-3 transition">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">🛒</div>
                <div><div class="font-bold text-xs text-white">سندوقی فرۆشتن</div><div class="text-[10px] text-slate-400">POS Checkout</div></div>
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="bg-slate-800/80 hover:bg-slate-700 border border-slate-700 p-3.5 rounded-2xl flex items-center gap-3 transition">
                <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">📦</div>
                <div><div class="font-bold text-xs text-white">کۆگا و کاڵاکان</div><div class="text-[10px] text-slate-400">ستۆک و کڕین</div></div>
            </a>
            <a href="{{ route('admin.debts.index') }}" class="bg-slate-800/80 hover:bg-slate-700 border border-slate-700 p-3.5 rounded-2xl flex items-center gap-3 transition">
                <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold">👥</div>
                <div><div class="font-bold text-xs text-white">دەفتەری قەرز</div><div class="text-[10px] text-slate-400">کڕیار و دابینکەر</div></div>
            </a>
            <a href="{{ route('admin.expenses.index') }}" class="bg-slate-800/80 hover:bg-slate-700 border border-slate-700 p-3.5 rounded-2xl flex items-center gap-3 transition">
                <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold">💸</div>
                <div><div class="font-bold text-xs text-white">خەرجییەکان</div><div class="text-[10px] text-slate-400">کرێ، کارەبا، مووچە</div></div>
            </a>
            <a href="{{ route('admin.promotions.index') }}" class="bg-slate-800/80 hover:bg-slate-700 border border-slate-700 p-3.5 rounded-2xl flex items-center gap-3 transition">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">🎁</div>
                <div><div class="font-bold text-xs text-white">ئۆفەر و داشکاندن</div><div class="text-[10px] text-slate-400">{{ $activePromosCount }} ئۆفەر</div></div>
            </a>
            <a href="{{ route('admin.access.index') }}" class="bg-slate-800/80 hover:bg-slate-700 border border-indigo-500/30 p-3.5 rounded-2xl flex items-center gap-3 transition">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">🛡️</div>
                <div><div class="font-bold text-xs text-white">کارمەندان و ڕۆڵ</div><div class="text-[10px] text-indigo-300 font-medium">مۆڵەتەکان (RBAC)</div></div>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="bg-slate-800/80 hover:bg-slate-700 border border-slate-700 p-3.5 rounded-2xl flex items-center gap-3 transition">
                <div class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center font-bold">⚙️</div>
                <div><div class="font-bold text-xs text-white">ڕێکخستنەکان</div><div class="text-[10px] text-slate-400">زانیاریی وەسڵ</div></div>
            </a>
        </div>

        <!-- کارتەکانی ئامار لەگەڵ هێمای دراو و دۆلار بەپێی Settings -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-5">
                <span class="text-xs font-bold text-slate-400">کۆی فرۆشی ئەمڕۆ</span>
                <div class="text-2xl font-black text-white mt-1.5 font-mono">
                    {{ number_format($totalSales, 0) }} 
                    <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 mt-2 pt-2 border-t border-slate-700/50">
                    <div><span class="text-emerald-400 font-bold font-mono">{{ $totalOrdersCount }}</span> پسوولە</div>
                    <div class="font-mono text-emerald-400 font-bold">≈ ${{ number_format($totalSalesUSD, 2) }}</div>
                </div>
            </div>

            <div class="bg-slate-800/80 border border-emerald-500/30 rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
                <span class="text-xs font-bold text-emerald-400">قازانجی سافی (Net Profit)</span>
                <div class="text-2xl font-black text-emerald-400 mt-1.5 font-mono">
                    {{ number_format($netProfit, 0) }} 
                    <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
                </div>
                <div class="text-xs text-slate-400 mt-2">تێچووی خەرجی: <span class="text-rose-400 font-mono font-bold">{{ number_format($todayExpenses, 0) }}</span> {{ $settings['currency_symbol'] ?? 'د.ع' }}</div>
            </div>

            <div class="bg-slate-800/80 border border-amber-500/30 rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
                <span class="text-xs font-bold text-amber-400">قەرزی لای کڕیاران</span>
                <div class="text-2xl font-black text-amber-400 mt-1.5 font-mono">
                    {{ number_format($customerDebt, 0) }} 
                    <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
                </div>
                <div class="text-xs text-slate-400 mt-2"><a href="{{ route('admin.debts.index') }}" class="text-amber-400 hover:underline">دەفتەری قەرز &larr;</a></div>
            </div>

            <div class="bg-slate-800/80 border border-rose-500/30 rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-rose-500"></div>
                <span class="text-xs font-bold text-rose-400">قەرزی دابینکەران</span>
                <div class="text-2xl font-black text-rose-400 mt-1.5 font-mono">
                    {{ number_format($supplierDebt, 0) }} 
                    <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
                </div>
                <div class="text-xs text-slate-400 mt-2"><a href="{{ route('admin.debts.index') }}" class="text-rose-400 hover:underline">حیسابی کۆمپانیاکان &larr;</a></div>
            </div>
        </div>

        <!-- خشتەی فرۆش و شیفتەکان -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 bg-slate-800/80 border border-slate-700/80 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-700/80 flex justify-between items-center bg-slate-800">
                    <h2 class="font-bold text-sm text-white">دوایین پسوولەکانی فرۆشتن</h2>
                    <span class="text-xs text-slate-400">سەرجەم فرۆشەکان</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-900/60 text-slate-400 font-bold border-b border-slate-700/50">
                            <tr>
                                <th class="p-3">ژمارەی پسوولە</th>
                                <th class="p-3">کاشێر</th>
                                <th class="p-3">شێوازی پارەدان</th>
                                <th class="p-3 text-left">کۆی گشتی ({{ $settings['currency_symbol'] ?? 'د.ع' }})</th>
                                <th class="p-3 text-left">کات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/40 font-medium">
                            @forelse ($recentOrders as $order)
                                <tr class="hover:bg-slate-700/30 transition">
                                    <td class="p-3 font-mono font-bold text-emerald-400">{{ $order->invoice_number ?? $order->invoice_no }}</td>
                                    <td class="p-3 text-slate-300">{{ $order->user->name ?? 'کاشێر' }}</td>
                                    <td class="p-3">
                                        <span class="bg-slate-700 text-slate-300 px-2 py-0.5 rounded text-[11px] font-semibold">
                                            {{ $order->payment_method === 'cash' ? 'کاش' : ($order->payment_method === 'debt' ? 'قەرز' : ($order->payment_method === 'cod' ? 'گەیاندن COD' : 'کارت')) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-left font-mono font-bold text-white">{{ number_format((float) $order->grand_total, 0) }}</td>
                                    <td class="p-3 text-left font-mono text-slate-400">{{ $order->created_at->format('H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-6 text-center text-slate-500">هیچ پسوولەیەک تۆمار نەکراوە.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 shadow-sm">
                    <h2 class="font-bold text-sm text-white border-b border-slate-700 pb-3 mb-3">شیفتەکانی کاشێر و چاپی Z-Report</h2>
                    <div class="space-y-2.5">
                        @forelse ($shifts as $shift)
                            <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-700/50 flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-slate-200">{{ $shift->user->name ?? 'کاشێر' }} ({{ $shift->register->name ?? 'REG-01' }})</p>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $shift->opened_at ? \Carbon\Carbon::parse($shift->opened_at)->format('m/d H:i') : '' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.reports.z_report', $shift->id) }}" target="_blank" class="bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/30 px-2 py-1 rounded text-[10px] font-bold transition">
                                        چاپی Z
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 text-center py-4">هیچ شیفتێک تۆمار نەکراوە.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 shadow-sm">
                    <h2 class="font-bold text-sm text-white border-b border-slate-700 pb-3 mb-3">پڕفرۆشترین کاڵاکان</h2>
                    <div class="space-y-3">
                        @forelse ($topProducts as $top)
                            <div class="flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-slate-200">{{ $top->name }}</p>
                                    <span class="text-slate-400 font-mono">{{ (int) $top->total_qty }} دانە</span>
                                </div>
                                <span class="font-mono font-bold text-emerald-400">{{ number_format((float) $top->total_sales, 0) }} {{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 text-center py-4">زانیاری بەردەست نییە.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- سکریپتی چاودێری ڕاستەوخۆی ئاگادارییەکان -->
    <script>
        let lastNotifId = 0;
        let isFirstLoad = true;

        function toggleNotifDropdown() {
            const el = document.getElementById('notif-dropdown');
            el.classList.toggle('hidden');
        }

        function markAllAsRead() {
            fetch("{{ route('admin.notifications.mark_read') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            }).then(() => {
                document.getElementById('notif-badge').classList.add('hidden');
                document.getElementById('notif-count-text').innerText = '0 نوێ';
            });
        }

        function showLiveToast(notif) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const borderColors = {
                'danger': 'border-rose-500 bg-slate-900 text-rose-300',
                'warning': 'border-amber-500 bg-slate-900 text-amber-300',
                'info': 'border-emerald-500 bg-slate-900 text-emerald-300'
            };

            const icons = {
                'danger': '🚨',
                'warning': '⚠️',
                'info': '🔔'
            };

            const colorClass = borderColors[notif.severity] || borderColors['info'];
            const icon = icons[notif.severity] || '🔔';

            toast.className = `pointer-events-auto p-4 rounded-2xl border shadow-2xl transition transform duration-300 translate-y-2 opacity-100 flex items-start gap-3 ${colorClass}`;
            toast.innerHTML = `
                <div class="text-xl">${icon}</div>
                <div class="flex-1">
                    <h4 class="font-black text-xs text-white">${notif.title}</h4>
                    <p class="text-[11px] text-slate-300 mt-0.5">${notif.message}</p>
                </div>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('opacity-0', '-translate-y-2');
                setTimeout(() => toast.remove(), 400);
            }, 6000);
        }

        function pollNotifications() {
            fetch("{{ route('admin.notifications.poll') }}?last_id=" + lastNotifId)
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('notif-badge');
                    const countText = document.getElementById('notif-count-text');
                    const list = document.getElementById('notif-list');

                    if (data.unread_count > 0) {
                        badge.innerText = data.unread_count;
                        badge.classList.remove('hidden');
                        countText.innerText = data.unread_count + ' نوێ';
                    } else {
                        badge.classList.add('hidden');
                        countText.innerText = '0 نوێ';
                    }

                    if (data.notifications && data.notifications.length > 0) {
                        list.innerHTML = data.notifications.map(n => `
                            <div class="p-3 hover:bg-slate-700/40 transition flex items-start gap-2.5 ${!n.is_read ? 'bg-slate-700/20' : ''}">
                                <span class="text-sm mt-0.5">${n.severity === 'danger' ? '🚨' : (n.severity === 'warning' ? '⚠️' : 'ℹ️')}</span>
                                <div class="flex-1">
                                    <div class="font-bold text-white text-[11px]">${n.title}</div>
                                    <div class="text-slate-300 text-[10px] mt-0.5">${n.message}</div>
                                </div>
                            </div>
                        `).join('');
                    }

                    if (!isFirstLoad && data.new && data.new.length > 0) {
                        data.new.forEach(item => showLiveToast(item));
                    }

                    if (data.max_id) {
                        lastNotifId = data.max_id;
                    }
                    isFirstLoad = false;
                })
                .catch(err => console.error(err));
        }

        setInterval(pollNotifications, 3500);
        pollNotifications();
    </script>

</body>
</html>
