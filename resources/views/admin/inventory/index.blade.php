@extends('layouts.admin')

@section('title', 'کۆگا و کاڵاکان')

@section('content')
    @if(session('success'))
        <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 p-4 rounded-2xl text-xs font-bold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-950/80 border border-rose-500/40 text-rose-300 p-4 rounded-2xl text-xs font-bold space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

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

    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-900">
            <h2 class="font-bold text-sm text-white">لیستی سەرجەم کاڵاکان و ستۆکی بەردەست</h2>
            <span class="text-xs text-slate-400 font-mono">کۆی کاڵا: {{ $products->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950/60 text-slate-400 font-bold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">ناوی کاڵا</th>
                        <th class="p-3.5">بارکۆد</th>
                        <th class="p-3.5">کەرت (Category)</th>
                        <th class="p-3.5">نرخی فرۆشتن ({{ $settings['currency_symbol'] ?? 'د.ع' }})</th>
                        <th class="p-3.5 text-center">ستۆکی بەردەست</th>
                        <th class="p-3.5 text-left">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @php
                        $alertThreshold = (float) ($settings['low_stock_alert'] ?? 5);
                    @endphp
                    @forelse ($products as $p)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-3.5 font-bold text-white">{{ $p->name }}</td>
                            <td class="p-3.5 font-mono text-emerald-400">{{ $p->barcodes->first()->code ?? '-' }}</td>
                            <td class="p-3.5 text-slate-300">{{ $p->category->name ?? '-' }}</td>
                            <td class="p-3.5 font-mono font-bold text-slate-200">{{ number_format((float) $p->retail_price, 0) }}</td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold {{ ((float)$p->current_stock > $alertThreshold) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30 animate-pulse' }}">
                                    {{ (float) $p->current_stock }} {{ $p->unit->short_code ?? '' }}
                                </span>
                            </td>
                            <td class="p-3.5 text-left">
                                <a href="{{ route('admin.inventory.label', $p->id) }}" target="_blank" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg text-[11px] font-semibold transition border border-slate-700">
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

        <div class="p-4 border-t border-slate-800/60">
            {{ $products->links() }}
        </div>
    </div>

    <!-- مۆداڵەکان -->
    <div id="modal-add-product" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-lg w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">تۆمارکردنی کاڵای نوێ</h3>
                <button onclick="document.getElementById('modal-add-product').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.inventory.product.store') }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی کاڵا:</label>
                    <input type="text" name="name" required placeholder="وەک: برنجی مەحمود ٥ کیلۆ" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کۆدی SKU:</label>
                        <input type="text" name="sku" required placeholder="RICE-05" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بارکۆد:</label>
                        <input type="text" name="barcode" required placeholder="869000000005" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کەرت:</label>
                        <select name="category_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">یەکە:</label>
                        <select name="unit_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">جۆری بارکۆد:</label>
                        <select name="barcode_type" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            <option value="unit">دانە (Unit)</option>
                            <option value="weight">تەرازوو (Weight)</option>
                            <option value="pack">پاکەت (Pack)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">نرخی فرۆشتن ({{ $settings['currency_symbol'] ?? 'د.ع' }}):</label>
                        <input type="number" step="250" name="retail_price" required placeholder="2500" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">تێچووی کڕین ({{ $settings['currency_symbol'] ?? 'د.ع' }}):</label>
                        <input type="number" step="250" name="cost_price" placeholder="1800" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ستۆکی سەرەتایی:</label>
                        <input type="number" step="1" name="initial_stock" placeholder="50" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کۆگا:</label>
                        <select name="warehouse_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-product').classList.add('hidden')" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-add-purchase" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">تۆمارکردنی پسوولەی کڕین</h3>
                <button onclick="document.getElementById('modal-add-purchase').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.inventory.purchase.store') }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">کاڵا هەڵبژێرە:</label>
                    <select name="product_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">کۆگا:</label>
                    <select name="warehouse_id" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بڕی کڕدراو:</label>
                        <input type="number" step="0.5" name="quantity" required placeholder="100" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">تێچووی کڕین بۆ یەکە ({{ $settings['currency_symbol'] ?? 'د.ع' }}):</label>
                        <input type="number" step="250" name="cost_price" required placeholder="1200" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">کۆدی باچ (ئیختیاری):</label>
                    <input type="text" name="batch_code" placeholder="BATCH-2026-08" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-blue-500">
                </div>
                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردن لە کۆگا</button>
                    <button type="button" onclick="document.getElementById('modal-add-purchase').classList.add('hidden')" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>
@endsection
