<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ڕێکخستنەکانی سیستەم - SuperMarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> * { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">

    <header class="bg-slate-950/80 border-b border-slate-800 px-6 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-xl text-slate-950">S</div>
            <div>
                <h1 class="font-extrabold text-base text-white">ڕێکخستنەکانی سوپەرمارکێت و پسوولە</h1>
                <p class="text-xs text-slate-400">دەستکاریکردنی ناوی مارکێت، زانیارییەکانی پەیوەندی و تێکستی سەر پسوولە</p>
            </div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="bg-slate-800 text-slate-300 text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-700">داشبۆرد</a>
    </header>

    <main class="flex-1 p-6 max-w-4xl w-full mx-auto space-y-6">
        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-6 shadow-sm">
            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی سوپەرمارکێت (لەسەر پسوولە):</label>
                    <input type="text" name="market_name" value="{{ $settings['market_name'] }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ژمارەی تەلەفۆن:</label>
                        <input type="text" name="phone" value="{{ $settings['phone'] }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white font-mono outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ناونیشان:</label>
                        <input type="text" name="address" value="{{ $settings['address'] }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">نامەی خوارەوەی پسوولەی فرۆشتن (Footer Note):</label>
                    <textarea name="receipt_footer" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white outline-none">{{ $settings['receipt_footer'] }}</textarea>
                </div>

                <div class="pt-3">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-2.5 rounded-xl transition">
                        پاشەکەوتکردنی ڕێکخستنەکان
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
