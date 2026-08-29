@extends('layouts.admin')

@section('title', 'ئۆفەر و داشکاندنەکان')

@section('content')
    @if(session('success'))
        <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 p-4 rounded-2xl text-xs font-bold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-extrabold text-base text-white">بەڕێوەبردنی ئۆفەر و داشکاندنەکان</h2>
            <p class="text-xs text-slate-400">داشکاندنی ڕێژەیی، نرخی تایبەت و ئۆفەری کڕین</p>
        </div>
        <button onclick="document.getElementById('modal-add-promo').classList.remove('hidden')" class="bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-purple-600/20 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>زیادکردنی ئۆفەری نوێ</span>
        </button>
    </div>

    <!-- خشتەی ئۆفەرەکان -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-900">
            <h3 class="font-bold text-sm text-white">لیستی ئۆفەرە چالاکەکان</h3>
            <span class="text-xs text-slate-400 font-mono">کۆی گشتی: {{ $promotions->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950/60 text-slate-400 font-bold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">ناوی ئۆفەر</th>
                        <th class="p-3.5">کاڵای دیاریکراو</th>
                        <th class="p-3.5">جۆری داشکاندن</th>
                        <th class="p-3.5">بڕی داشکاندن</th>
                        <th class="p-3.5">ماوەی چالاکبوون</th>
                        <th class="p-3.5 text-center">دۆخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse ($promotions as $promo)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-3.5 font-bold text-white">{{ $promo->name }}</td>
                            <td class="p-3.5 text-slate-300">{{ $promo->product_name ?? 'سەرجەم کاڵاکان' }}</td>
                            <td class="p-3.5">
                                <span class="bg-slate-800 text-slate-300 px-2 py-0.5 rounded text-[11px] font-semibold border border-slate-700/50">
                                    {{ $promo->type === 'percentage' ? 'داشکاندنی ڕێژەیی (%)' : ($promo->type === 'fixed_price' ? 'نرخی دیاریکراو' : 'Buy X Get Y') }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-emerald-400 text-sm">
                                {{ $promo->type === 'percentage' ? $promo->discount_value . '%' : number_format((float) $promo->discount_value, 0) . ' ' . ($settings['currency_symbol'] ?? 'د.ع') }}
                            </td>
                            <td class="p-3.5 font-mono text-slate-400">
                                {{ $promo->start_date ? $promo->start_date . ' بۆ ' . ($promo->end_date ?? 'بەردەوام') : 'هەمیشەیی' }}
                            </td>
                            <td class="p-3.5 text-center">
                                <form action="{{ route('admin.promotions.toggle', $promo->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $promo->is_active ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-500 border border-slate-700' }}">
                                        {{ $promo->is_active ? 'چالاکە' : 'ناچالاکە' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">هیچ ئۆفەرێک تۆمار نەکراوە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/60">
            {{ $promotions->links() }}
        </div>
    </div>

    <!-- مۆداڵی زیادکردنی ئۆفەر -->
    <div id="modal-add-promo" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">تۆمارکردنی ئۆفەری نوێ</h3>
                <button onclick="document.getElementById('modal-add-promo').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.promotions.store') }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناوی ئۆفەر:</label>
                    <input type="text" name="name" required placeholder="وەک: داشکاندنی جەژن یان ئۆفەری شیرینی" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-purple-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">جۆری ئۆفەر:</label>
                        <select name="type" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            <option value="percentage">داشکاندنی سەدی (%)</option>
                            <option value="fixed_price">نرخی دیاریکراو</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بڕی داشکاندن:</label>
                        <input type="number" step="0.5" name="discount_value" required placeholder="10" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">کاڵای دیاریکراو (ئیختیاری):</label>
                    <select name="product_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                        <option value="">سەرجەم کاڵاکان</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">دەستپێک:</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کۆتایی:</label>
                        <input type="date" name="end_date" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-purple-500">
                    </div>
                </div>

                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-promo').classList.add('hidden')" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>
@endsection
