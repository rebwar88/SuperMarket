<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ئۆفەر و داشکاندنەکان - SuperMarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> * { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">

    <header class="bg-slate-950/80 border-b border-slate-800 px-6 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-xl text-slate-950">S</div>
            <div>
                <h1 class="font-extrabold text-base text-white">بەڕێوەبردنی ئۆفەرەکان و داشکاندن</h1>
                <p class="text-xs text-slate-400">ئۆفەری وەرزی، داشکاندنی ڕێژەیی و کڕینی دانەیەک و یەکێک بە خۆڕایی (BOGO)</p>
            </div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="bg-slate-800 text-slate-300 text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-700">داشبۆرد</a>
    </header>

    <main class="flex-1 p-6 max-w-7xl w-full mx-auto space-y-6">
        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-end">
            <button onclick="document.getElementById('modal-add-promo').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
                + دروستکردنی ئۆفەری نوێ
            </button>
        </div>

        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl overflow-hidden shadow-sm">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-900/60 text-slate-400 font-bold border-b border-slate-700/50">
                    <tr>
                        <th class="p-3.5">ناوی ئۆفەر</th>
                        <th class="p-3.5">کاڵا</th>
                        <th class="p-3.5">جۆری ئۆفەر</th>
                        <th class="p-3.5">بڕی داشکاندن / دیاری</th>
                        <th class="p-3.5">ماوەی چالاکبوون</th>
                        <th class="p-3.5 text-center">دۆخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40">
                    @forelse($promotions as $promo)
                        <tr class="hover:bg-slate-700/30">
                            <td class="p-3.5 font-bold text-white">{{ $promo->name }}</td>
                            <td class="p-3.5 text-slate-300">{{ $promo->product->name ?? '-' }}</td>
                            <td class="p-3.5">
                                <span class="bg-slate-700 px-2 py-0.5 rounded text-[11px] font-semibold">
                                    {{ $promo->type === 'percentage' ? 'ڕێژەی %' : ($promo->type === 'bogo' ? 'BOGO دیاری' : 'داشکاندنی نەختینە') }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-emerald-400">
                                @if($promo->type === 'percentage')
                                    {{ $promo->discount_value }}%
                                @elseif($promo->type === 'bogo')
                                    {{ $promo->buy_quantity }} بکڕە + {{ $promo->get_quantity }} بە خۆڕایی
                                @else
                                    {{ number_format((float) $promo->discount_value, 0) }} د.ع
                                @endif
                            </td>
                            <td class="p-3.5 font-mono text-slate-400 text-[11px]">{{ $promo->start_date }} تا {{ $promo->end_date }}</td>
                            <td class="p-3.5 text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/20 text-emerald-400">چالاکە</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-slate-500">هیچ ئۆفەرێک دیاری نەکراوە.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>

    <!-- مۆداڵی ئۆفەر -->
    <div id="modal-add-promo" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-md w-full rounded-2xl shadow-2xl p-5 space-y-4">
            <h3 class="font-bold text-sm text-white">دروستکردنی ئۆفەری نوێ</h3>
            <form action="{{ route('admin.promotions.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی ئۆفەر:</label>
                    <input type="text" name="name" required placeholder="داشکاندنی کۆتایی هەفتە" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">کاڵا:</label>
                    <select name="product_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ number_format((float)$p->retail_price, 0) }} د.ع)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">جۆری داشکاندن:</label>
                    <select name="type" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                        <option value="percentage">داشکاندن بە ڕێژەی لەسەدا (%)</option>
                        <option value="fixed_discount">داشکاندنی نەختینە بە بڕی دیاریکراو (د.ع)</option>
                        <option value="bogo">دانەیەک بکڕە + دیاری وەربگرە (BOGO)</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بڕی داشکاندن (% یان د.ع):</label>
                        <input type="number" step="0.5" name="discount_value" placeholder="10" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بڕی کڕین بۆ BOGO:</label>
                        <input type="number" step="1" name="buy_quantity" placeholder="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">دەستپێک:</label>
                        <input type="datetime-local" name="start_date" value="{{ date('Y-m-d\T00:00') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کۆتایی:</label>
                        <input type="datetime-local" name="end_date" value="{{ date('Y-m-d\T23:59', strtotime('+7 days')) }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-2 py-2 text-white outline-none">
                    </div>
                </div>
                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-xl">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-promo').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-2.5 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
