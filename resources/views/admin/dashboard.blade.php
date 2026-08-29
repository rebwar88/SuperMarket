@extends('layouts.admin')

@section('title', 'داشبۆردی سەرەکی')

@section('content')
    <!-- دوگمە خێراکان -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
        <a href="{{ route('pos.index') }}" class="bg-slate-900 hover:bg-slate-800 border border-slate-800 p-3.5 rounded-2xl flex items-center gap-3 transition">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">🛒</div>
            <div><div class="font-bold text-xs text-white">سندوقی فرۆشتن</div><div class="text-[10px] text-slate-400">POS Checkout</div></div>
        </a>
        <a href="{{ route('admin.inventory.index') }}" class="bg-slate-900 hover:bg-slate-800 border border-slate-800 p-3.5 rounded-2xl flex items-center gap-3 transition">
            <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">📦</div>
            <div><div class="font-bold text-xs text-white">کۆگا و کاڵاکان</div><div class="text-[10px] text-slate-400">ستۆک و کڕین</div></div>
        </a>
        <a href="{{ route('admin.debts.index') }}" class="bg-slate-900 hover:bg-slate-800 border border-slate-800 p-3.5 rounded-2xl flex items-center gap-3 transition">
            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold">👥</div>
            <div><div class="font-bold text-xs text-white">دەفتەری قەرز</div><div class="text-[10px] text-slate-400">کڕیار و دابینکەر</div></div>
        </a>
        <a href="{{ route('admin.expenses.index') }}" class="bg-slate-900 hover:bg-slate-800 border border-slate-800 p-3.5 rounded-2xl flex items-center gap-3 transition">
            <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold">💸</div>
            <div><div class="font-bold text-xs text-white">خەرجییەکان</div><div class="text-[10px] text-slate-400">کرێ، کارەبا، مووچە</div></div>
        </a>
        <a href="{{ route('admin.promotions.index') }}" class="bg-slate-900 hover:bg-slate-800 border border-slate-800 p-3.5 rounded-2xl flex items-center gap-3 transition">
            <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">🎁</div>
            <div><div class="font-bold text-xs text-white">ئۆفەر و داشکاندن</div><div class="text-[10px] text-slate-400">{{ $activePromosCount }} ئۆفەر</div></div>
        </a>
        <a href="{{ route('admin.access.index') }}" class="bg-slate-900 hover:bg-slate-800 border border-indigo-500/30 p-3.5 rounded-2xl flex items-center gap-3 transition">
            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">🛡️</div>
            <div><div class="font-bold text-xs text-white">کارمەندان و ڕۆڵ</div><div class="text-[10px] text-indigo-300 font-medium">مۆڵەتەکان (RBAC)</div></div>
        </a>
        <a href="{{ route('admin.settings.index') }}" class="bg-slate-900 hover:bg-slate-800 border border-slate-800 p-3.5 rounded-2xl flex items-center gap-3 transition">
            <div class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center font-bold">⚙️</div>
            <div><div class="font-bold text-xs text-white">ڕێکخستنەکان</div><div class="text-[10px] text-slate-400">کۆنتڕۆڵی گشتی</div></div>
        </a>
    </div>

    <!-- کارتەکانی ئامار -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <span class="text-xs font-bold text-slate-400">کۆی فرۆشی ئەمڕۆ</span>
            <div class="text-2xl font-black text-white mt-1.5 font-mono">
                {{ number_format($totalSales, 0) }} 
                <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-400 mt-2 pt-2 border-t border-slate-800/80">
                <div><span class="text-emerald-400 font-bold font-mono">{{ $totalOrdersCount }}</span> پسوولە</div>
                <div class="font-mono text-emerald-400 font-bold">≈ ${{ number_format($totalSalesUSD, 2) }}</div>
            </div>
        </div>

        <div class="bg-slate-900 border border-emerald-500/30 rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
            <span class="text-xs font-bold text-emerald-400">قازانجی سافی (Net Profit)</span>
            <div class="text-2xl font-black text-emerald-400 mt-1.5 font-mono">
                {{ number_format($netProfit, 0) }} 
                <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
            </div>
            <div class="text-xs text-slate-400 mt-2">تێچووی خەرجی: <span class="text-rose-400 font-mono font-bold">{{ number_format($todayExpenses, 0) }}</span> {{ $settings['currency_symbol'] ?? 'د.ع' }}</div>
        </div>

        <div class="bg-slate-900 border border-amber-500/30 rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <span class="text-xs font-bold text-amber-400">قەرزی لای کڕیاران</span>
            <div class="text-2xl font-black text-amber-400 mt-1.5 font-mono">
                {{ number_format($customerDebt, 0) }} 
                <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
            </div>
            <div class="text-xs text-slate-400 mt-2"><a href="{{ route('admin.debts.index') }}" class="text-amber-400 hover:underline">دەفتەری قەرز &larr;</a></div>
        </div>

        <div class="bg-slate-900 border border-rose-500/30 rounded-2xl p-5 relative overflow-hidden">
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
        <div class="lg:col-span-8 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-900">
                <h2 class="font-bold text-sm text-white">دوایین پسوولەکانی فرۆشتن</h2>
                <span class="text-xs text-slate-400">سەرجەم فرۆشەکان</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-950/60 text-slate-400 font-bold border-b border-slate-800">
                        <tr>
                            <th class="p-3">ژمارەی پسوولە</th>
                            <th class="p-3">کاشێر</th>
                            <th class="p-3">شێوازی پارەدان</th>
                            <th class="p-3 text-left">کۆی گشتی ({{ $settings['currency_symbol'] ?? 'د.ع' }})</th>
                            <th class="p-3 text-left">کات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        @forelse ($recentOrders as $order)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-3 font-mono font-bold text-emerald-400">{{ $order->invoice_number ?? $order->invoice_no }}</td>
                                <td class="p-3 text-slate-300">{{ $order->user->name ?? 'کاشێر' }}</td>
                                <td class="p-3">
                                    <span class="bg-slate-800 text-slate-300 px-2 py-0.5 rounded text-[11px] font-semibold">
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
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
                <h2 class="font-bold text-sm text-white border-b border-slate-800 pb-3 mb-3">شیفتەکانی کاشێر و چاپی Z-Report</h2>
                <div class="space-y-2.5">
                    @forelse ($shifts as $shift)
                        <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center justify-between text-xs">
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

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
                <h2 class="font-bold text-sm text-white border-b border-slate-800 pb-3 mb-3">پڕفرۆشترین کاڵاکان</h2>
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
@endsection
