@extends('layouts.admin')

@section('title', 'بەڕێوەبردنی خەرجییەکان')

@section('content')
    @if(session('success'))
        <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 p-4 rounded-2xl text-xs font-bold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- کارتەکانی پوختەی خەرجی -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-slate-900 border border-rose-500/30 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-rose-500"></div>
            <span class="text-xs font-bold text-rose-400">کۆی خەرجی ئەم مانگە</span>
            <div class="text-2xl font-black text-rose-400 mt-1.5 font-mono">
                {{ number_format((float) $totalExpensesThisMonth, 0) }} <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
            </div>
            <div class="text-xs text-slate-400 mt-2">خەرجییەکانی مانگی {{ now()->format('m/Y') }}</div>
        </div>

        <div class="bg-slate-900 border border-amber-500/30 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <span class="text-xs font-bold text-amber-400">خەرجییەکانی ئەمڕۆ</span>
            <div class="text-2xl font-black text-amber-400 mt-1.5 font-mono">
                {{ number_format((float) $totalExpensesToday, 0) }} <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
            </div>
            <div class="text-xs text-slate-400 mt-2">بەروار: {{ now()->format('Y-m-d') }}</div>
        </div>
    </div>

    <!-- دوگمەی زیادکردن -->
    <div class="flex justify-between items-center">
        <button onclick="document.getElementById('modal-add-expense').classList.remove('hidden')" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-rose-600/20 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>تۆمارکردنی خەرجی نوێ</span>
        </button>
    </div>

    <!-- خشتەی خەرجییەکان -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-900">
            <h2 class="font-bold text-sm text-white">لیستی خەرجییە تۆمارکراوەکان</h2>
            <span class="text-xs text-slate-400 font-mono">کۆی گشتی: {{ $expenses->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-950/60 text-slate-400 font-bold border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">ناونیشانی خەرجی</th>
                        <th class="p-3.5">کەرت (Category)</th>
                        <th class="p-3.5">بڕی خەرجی ({{ $settings['currency_symbol'] ?? 'د.ع' }})</th>
                        <th class="p-3.5">بەروار</th>
                        <th class="p-3.5">تێبینی</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse ($expenses as $exp)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-3.5 font-bold text-white">{{ $exp->title }}</td>
                            <td class="p-3.5">
                                <span class="bg-slate-800 text-slate-300 px-2 py-0.5 rounded text-[11px] font-semibold border border-slate-700/50">
                                    {{ $exp->category_name ?? 'گشتی' }}
                                </span>
                            </td>
                            <td class="p-3.5 font-mono font-bold text-rose-400 text-sm">
                                {{ number_format((float) $exp->amount, 0) }}
                            </td>
                            <td class="p-3.5 font-mono text-slate-400">{{ $exp->expense_date }}</td>
                            <td class="p-3.5 text-slate-400">{{ $exp->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">هیچ خەرجییەک تۆمار نەکراوە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/60">
            {{ $expenses->links() }}
        </div>
    </div>

    <!-- مۆداڵی تۆمارکردنی خەرجی -->
    <div id="modal-add-expense" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">تۆمارکردنی خەرجی</h3>
                <button onclick="document.getElementById('modal-add-expense').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.expenses.store') }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناونیشانی خەرجی:</label>
                    <input type="text" name="title" required placeholder="وەک: پارەی کارەبای مانگی ٨" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-rose-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بڕی پارە ({{ $settings['currency_symbol'] ?? 'د.ع' }}):</label>
                        <input type="number" step="250" name="amount" required placeholder="50000" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کەرت:</label>
                        <select name="category_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            <option value="">هەڵبژاردنی کەرت...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">بەروار:</label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-rose-500">
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">تێبینی:</label>
                    <textarea name="notes" rows="2" placeholder="تێبینی زیادە ئەگەر پێویست بکات..." class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-white outline-none focus:border-rose-500"></textarea>
                </div>

                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-expense').classList.add('hidden')" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>
@endsection
