<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تۆمارکردنی خەرجییەکان - SuperMarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> * { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">

    <header class="bg-slate-950/80 border-b border-slate-800 px-6 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-xl text-slate-950">S</div>
            <div>
                <h1 class="font-extrabold text-base text-white">بەڕێوەبردنی خەرجییەکان</h1>
                <p class="text-xs text-slate-400">تۆمارکردنی خەرجی ڕۆژانە و مانگانە (کرێ، کارەبا، مووچە)</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="bg-slate-800 text-slate-300 text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-700">داشبۆرد</a>
        </div>
    </header>

    <main class="flex-1 p-6 max-w-7xl w-full mx-auto space-y-6">
        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between">
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-4 flex items-center gap-4">
                <span class="text-xs font-bold text-slate-400">کۆی خەرجی ئەم مانگە:</span>
                <span class="text-xl font-black text-rose-400 font-mono">{{ number_format((float) $totalExpensesThisMonth, 0) }} د.ع</span>
            </div>
            <button onclick="document.getElementById('modal-add-expense').classList.remove('hidden')" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-rose-600/20">
                + تۆمارکردنی خەرجی نوێ
            </button>
        </div>

        <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl overflow-hidden shadow-sm">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-900/60 text-slate-400 font-bold border-b border-slate-700/50">
                    <tr>
                        <th class="p-3.5">ناونیشانی خەرجی</th>
                        <th class="p-3.5">کاتیگۆری</th>
                        <th class="p-3.5">بڕی پارە (د.ع)</th>
                        <th class="p-3.5">بەروار</th>
                        <th class="p-3.5">تێبینی</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40">
                    @forelse($expenses as $e)
                        <tr class="hover:bg-slate-700/30">
                            <td class="p-3.5 font-bold text-white">{{ $e->title }}</td>
                            <td class="p-3.5 text-slate-300">{{ $e->category }}</td>
                            <td class="p-3.5 font-mono font-black text-rose-400">{{ number_format((float) $e->amount, 0) }}</td>
                            <td class="p-3.5 font-mono text-slate-400">{{ $e->expense_date }}</td>
                            <td class="p-3.5 text-slate-400">{{ $e->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-slate-500">هیچ خەرجییەک تۆمار نەکراوە.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>

    <!-- مۆداڵی خەرجی نوێ -->
    <div id="modal-add-expense" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-md w-full rounded-2xl shadow-2xl p-5 space-y-4">
            <h3 class="font-bold text-sm text-white">تۆمارکردنی خەرجی نوێ</h3>
            <form action="{{ route('admin.expenses.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناونیشان:</label>
                    <input type="text" name="title" required placeholder="وەک: پارەی کارەبای موەلیدە" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">کاتیگۆری:</label>
                        <select name="category" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            <option value="کارەبا و ئاو">کارەبا و ئاو</option>
                            <option value="کرێ">کرێی بینا</option>
                            <option value="مووچە">مووچەی کارمەندان</option>
                            <option value="خاوێنکردنەوە">خاوێنکردنەوە و پێداویستی</option>
                            <option value="لاوەکی">خەرجی لاوەکی</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بڕی پارە (د.ع):</label>
                        <input type="number" step="250" name="amount" required placeholder="50000" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">بەروار:</label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">تێبینی:</label>
                    <input type="text" name="notes" placeholder="وردەکاری زیاتر..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none">
                </div>
                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white font-bold py-2.5 rounded-xl">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-expense').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-2.5 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
