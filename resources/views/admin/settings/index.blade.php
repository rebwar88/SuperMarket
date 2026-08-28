<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ڕێکخستنەکانی سیستەم - SuperMarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> * { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

    <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold hover:bg-emerald-500/30 transition">←</a>
            <div>
                <h1 class="font-extrabold text-base text-white">ڕێکخستنەکانی سیستەم (Settings)</h1>
                <p class="text-xs text-slate-400">کۆنتڕۆڵی تەواوی زانیارییەکانی فرۆشگا، وەسڵ و سندوق</p>
            </div>
        </div>
        <a href="{{ route('pos.index') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-xl text-xs transition flex items-center gap-1.5 shadow-lg shadow-emerald-600/20">
            <span>چوون بۆ سندوق (POS)</span>
            <span>↗</span>
        </a>
    </header>

    <main class="max-w-5xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 p-4 rounded-2xl text-xs font-bold flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- ١. زانیارییە گشتییەکانی فرۆشگا -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-sm font-bold">🏪</div>
                    <h2 class="font-bold text-sm text-white">زانیارییەکانی مارکێت</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">ناوی سوپەرمارکێت / پڕۆژە:</label>
                        <input type="text" name="market_name" value="{{ $settings['market_name'] ?? '' }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">ژمارەی پەیوەندی / مۆبایل:</label>
                        <input type="text" name="phone" value="{{ $settings['phone'] ?? '' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-slate-300 font-bold mb-1.5">ناونیشانی تەواو:</label>
                        <input type="text" name="address" value="{{ $settings['address'] ?? '' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- ٢. کۆنتڕۆڵی سندوق و ڕێساکانی پارەدان (POS Settings) -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-sm font-bold">💳</div>
                    <h2 class="font-bold text-sm text-white">کۆنتڕۆڵی سندوق (POS Controls)</h2>
                </div>

                <div class="space-y-3 text-xs">
                    <label class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800 rounded-2xl cursor-pointer hover:border-slate-700 transition">
                        <div>
                            <div class="font-bold text-white">ڕێگەدان بە فرۆشتنی قەرز (Pay Later)</div>
                            <div class="text-[11px] text-slate-400">ئەگەر ناچالاک بێت، دوگمەی Pay Later لە شاشەی کاشێرەکان دەشاردرێتەوە.</div>
                        </div>
                        <input type="checkbox" name="allow_pay_later" value="1" {{ ($settings['allow_pay_later'] ?? '1') === '1' ? 'checked' : '' }} class="w-5 h-5 rounded text-emerald-500 bg-slate-900 border-slate-700 focus:ring-0">
                    </label>

                    <label class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800 rounded-2xl cursor-pointer hover:border-slate-700 transition">
                        <div>
                            <div class="font-bold text-white">ڕێگەدان بە پارەدانی ئۆنلاین (FIB / Card Gateway)</div>
                            <div class="text-[11px] text-slate-400">چالاککردنی دوگمە و دەروازەی پارەدانی کارتی ئەلیکترۆنی لە سندوق.</div>
                        </div>
                        <input type="checkbox" name="allow_online_pay" value="1" {{ ($settings['allow_online_pay'] ?? '1') === '1' ? 'checked' : '' }} class="w-5 h-5 rounded text-blue-500 bg-slate-900 border-slate-700 focus:ring-0">
                    </label>

                    <label class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800 rounded-2xl cursor-pointer hover:border-slate-700 transition">
                        <div>
                            <div class="font-bold text-white">چاپی ئۆتۆماتیکی وەسڵ پاش فرۆشتن</div>
                            <div class="text-[11px] text-slate-400">کردنەوەی پەنجەرەی چاپ بە شێوەیەکی ئۆتۆماتیکی پاش تەواوبوونی مامەڵە.</div>
                        </div>
                        <input type="checkbox" name="auto_print_receipt" value="1" {{ ($settings['auto_print_receipt'] ?? '1') === '1' ? 'checked' : '' }} class="w-5 h-5 rounded text-indigo-500 bg-slate-900 border-slate-700 focus:ring-0">
                    </label>
                </div>
            </div>

            <!-- ٣. ڕێکخستنی وەسڵی چاپکراو (Receipt Template) -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-sm font-bold">🖨️</div>
                    <h2 class="font-bold text-sm text-white">ڕێکخستنی دەقی سەر و بنپەڕەی وەسڵ</h2>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">دەقی سەرەوەی وەسڵ (Receipt Header):</label>
                        <input type="text" name="receipt_header" value="{{ $settings['receipt_header'] ?? '' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">دەقی خوارەوە / مەرجی گەڕاندنەوە (Receipt Footer):</label>
                        <textarea name="receipt_footer" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-white outline-none focus:border-purple-500">{{ $settings['receipt_footer'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- ٤. ئاگادارییەکانی کۆگا و دراو -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm font-bold">📊</div>
                    <h2 class="font-bold text-sm text-white">دراو و ئاگاداری کۆگا</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">هێمای دراوی سەرەکی:</label>
                        <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? 'د.ع' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">نرخی ١٠٠ دۆلار (بە دینار):</label>
                        <input type="number" name="usd_exchange_rate" value="{{ $settings['usd_exchange_rate'] ?? '150000' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">ئاگاداری کەمبوونەوەی کاڵا (دانە):</label>
                        <input type="number" name="low_stock_alert" value="{{ $settings['low_stock_alert'] ?? '5' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <!-- دوگمەی پاشەکەوتکردن -->
            <div class="flex justify-end pt-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-8 py-3.5 rounded-2xl text-xs transition shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>پاشەکەوتکردنی هەموو ڕێکخستنەکان</span>
                </button>
            </div>

        </form>

    </main>

</body>
</html>
