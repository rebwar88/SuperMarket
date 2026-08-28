<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بەڕێوەبردنی کۆگا و کاڵاکان - SuperMarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">

    <!-- سەرپەڕەی سەرەکی -->
    <header class="bg-slate-950/80 border-b border-slate-800 px-6 py-3.5 flex items-center justify-between sticky top-0 z-40 backdrop-blur">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-xl text-slate-950 shadow-lg shadow-emerald-500/20">
                S
            </div>
            <div>
                <h1 class="font-extrabold text-base tracking-tight text-white">بەڕێوەبردنی کۆگا و کاڵاکان</h1>
                <p class="text-xs text-slate-400">زیادکردنی بەرهەم، چاپی بارکۆد و پسوولەی کڕین</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-700 transition">
                داشبۆردی سەرەکی
            </a>
            <a href="{{ route('pos.index') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-lg shadow-emerald-600/20">
                <span>شاشەی سندوق (POS)</span>
            </a>
        </div>
    </header>

    <main class="flex-1 p-6 max-w-7xl w-full mx-auto space-y-6">

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-xl text-sm font-semibold space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- بەشی دوگمەکانی کارپێکردن -->
        <div class="flex items-center justify-between">
            <div class="flex gap-2">
                <button onclick="document.getElementById('modal-add-product').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>زیادکردنی کاڵای نوێ</span>
                </button>
                <button onclick="document.getElementById('modal-add-purchase').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-blue-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>تۆمارکردنی پسوولەی کڕین (داخڵکردنی ستۆک)</span>
                </button>
            </div>
        </div>

        <!-- خشتەی سەرەکیی کاڵاکان -->
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-700/80 flex justify-between items-center bg-slate-800">
                <h2 class="font-bold text-sm text-white">لیستی سەرجەم کاڵاکان و ستۆکی بەردەست</h2>
                <span class="text-xs text-slate-400 font-mono">کۆی کاڵا: {{ $products->total() }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-900/60 text-slate-400 font-bold border-b border-slate-700/50">
                        <tr>
                            <th class="p-3.5">ناوی کاڵا</th>
                            <th class="p-3.5">بارکۆد</th>
                            <th class="p-3.5">کەرت (Category)</th>
                            <th class="p-3.5">نرخی فرۆشتن (د.ع)</th>
                            <th class="p-3.5 text-center">ستۆکی بەردەست</th>
                            <th class="p-3.5 text-left">کردارەکان</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/40 font-medium">
                        @forelse ($products as $p)
                            <tr class="hover:bg-slate-700/30 transition">
                                <td class="p-3.5 font-bold text-white">{{ $p->name }}</td>
                                <td class="p-3.5 font-mono text-emerald-400">{{ $p->barcodes->first()->code ?? '-' }}</td>
                                <td class="p-3.5 text-slate-300">{{ $p->category->name ?? '-' }}</td>
                                <td class="p-3.5 font-mono font-bold text-slate-200">{{ number_format((float) $p->retail_price, 0) }}</td>
                                <td class="p-3.5 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold {{ ((float)$p->current_stock > 10) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                                        {{ (float) $p->current_stock }} {{ $p->unit->short_code ?? '' }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-left">
                                    <a href="{{ route('admin.inventory.label', $p->id) }}" target="_blank" class="bg-slate-700 hover:bg-slate-600 text-slate-200 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition">
                                        چاپی لەیبڵ
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">هیچ کاڵایەک لە سیستەمدا نییە.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-700/50">
                {{ $products->links() }}
            </div>
        </div>

    </main>

    <!-- مۆداڵی زیادکردنی کاڵای نوێ -->
    <div id="modal-add-product" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-lg w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">تۆمارکردنی کاڵای نوێ</h3>
                <button onclick="document.getElementById('modal-add-product').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.inventory.product.store') }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی کاڵا:</label>
                    <input type="text" name="name" required placeholder="وەک: برنجی مەحمود ٥ کیلۆ" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کۆدی SKU:</label>
                        <input type="text" name="sku" required placeholder="RICE-05" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بارکۆد:</label>
                        <input type="text" name="barcode" required placeholder="869000000005" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کەرت:</label>
                        <select name="category_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">یەکە:</label>
                        <select name="unit_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">جۆری بارکۆد:</label>
                        <select name="barcode_type" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            <option value="unit">دانە (Unit)</option>
                            <option value="weight">تەرازوو (Weight)</option>
                            <option value="pack">پاکەت (Pack)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">نرخی فرۆشتن (د.ع):</label>
                        <input type="number" step="250" name="retail_price" required placeholder="2500" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">تێچووی کڕین (د.ع):</label>
                        <input type="number" step="250" name="cost_price" placeholder="1800" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ستۆکی سەرەتایی:</label>
                        <input type="number" step="1" name="initial_stock" placeholder="50" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کۆگا:</label>
                        <select name="warehouse_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-product').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مۆداڵی داخڵکردنی پسوولەی کڕین -->
    <div id="modal-add-purchase" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">تۆمارکردنی پسوولەی کڕین</h3>
                <button onclick="document.getElementById('modal-add-purchase').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.inventory.purchase.store') }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">کاڵا هەڵبژێرە:</label>
                    <select name="product_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">کۆگا:</label>
                    <select name="warehouse_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بڕی کڕدراو:</label>
                        <input type="number" step="0.5" name="quantity" required placeholder="100" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">تێچووی کڕین بۆ یەکە (د.ع):</label>
                        <input type="number" step="250" name="cost_price" required placeholder="1200" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">کۆدی باچ (ئیختیاری):</label>
                    <input type="text" name="batch_code" placeholder="BATCH-2026-08" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-blue-500">
                </div>

                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردن لە کۆگا</button>
                    <button type="button" onclick="document.getElementById('modal-add-purchase').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
