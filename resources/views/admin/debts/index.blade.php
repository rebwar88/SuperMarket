<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دەفتەری حیسابات و قەرزەکان - {{ $settings['market_name'] ?? 'SuperMarket' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> * { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">

    <!-- سەرپەڕە -->
    <header class="bg-slate-950/80 border-b border-slate-800 px-6 py-3.5 flex items-center justify-between sticky top-0 z-40 backdrop-blur">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-xl text-slate-950 shadow-lg shadow-emerald-500/20">
                S
            </div>
            <div>
                <h1 class="font-extrabold text-base tracking-tight text-white">دەفتەری حیسابات و قەرزەکان</h1>
                <p class="text-xs text-slate-400">
                    فرۆشتنی قەرز لە سندوق: 
                    @if(($settings['allow_pay_later'] ?? '1') === '1')
                        <span class="text-emerald-400 font-bold">چالاکە ✅</span>
                    @else
                        <span class="text-rose-400 font-bold">ناچالاککراوە 🔒</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-700 transition">
                داشبۆرد
            </a>
            <a href="{{ route('admin.inventory.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold px-4 py-2.5 rounded-xl border border-slate-700 transition">
                کۆگا و کاڵاکان
            </a>
            <a href="{{ route('pos.index') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-emerald-600/20">
                شاشەی سندوق (POS)
            </a>
        </div>
    </header>

    <main class="flex-1 p-6 max-w-7xl w-full mx-auto space-y-6">

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- کارتەکانی پوختەی قەرز لەگەڵ هێمای دراو بەپێی Settings -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-800/80 border border-amber-500/30 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
                <span class="text-xs font-bold text-amber-400">کۆی قەرزی کڕیاران (پارەیەک کە لە دەرەوەیە)</span>
                <div class="text-2xl font-black text-amber-400 mt-1.5 font-mono">
                    {{ number_format((float) $totalCustomerDebt, 0) }} <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
                </div>
                <div class="text-xs text-slate-400 mt-2 font-mono">
                    {{ $customers->count() }} کڕیار تۆمارکراوە
                </div>
            </div>

            <div class="bg-slate-800/80 border border-rose-500/30 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-rose-500"></div>
                <span class="text-xs font-bold text-rose-400">کۆی قەرزی دابینکەران (قەرزی سەر سوپەرمارکێت)</span>
                <div class="text-2xl font-black text-rose-400 mt-1.5 font-mono">
                    {{ number_format((float) $totalSupplierDebt, 0) }} <span class="text-xs text-slate-400 font-sans">{{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
                </div>
                <div class="text-xs text-slate-400 mt-2 font-mono">
                    {{ $suppliers->count() }} کۆمپانیا/دابینکەر
                </div>
            </div>
        </div>

        <!-- دوگمەی کردارەکان -->
        <div class="flex gap-2">
            <button onclick="document.getElementById('modal-add-party').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>زیادکردنی کڕیار / دابینکەر</span>
            </button>
            <button onclick="document.getElementById('modal-add-payment').classList.remove('hidden')" class="bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-amber-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>وەرگرتنەوە / پێدانی پارەی قەرز (سەند)</span>
            </button>
        </div>

        <!-- خشتەکان -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- لیستی کڕیاران و قەرزەکانیان -->
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-700/80 flex justify-between items-center bg-slate-800">
                    <h2 class="font-bold text-sm text-white">حیسابی قەرزی کڕیاران</h2>
                    <span class="text-xs text-amber-400 font-bold">داواکراو</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-900/60 text-slate-400 font-bold border-b border-slate-700/50">
                            <tr>
                                <th class="p-3">ناوی کڕیار</th>
                                <th class="p-3">ژمارەی تەلەفۆن</th>
                                <th class="p-3 text-left">قەرز ({{ $settings['currency_symbol'] ?? 'د.ع' }})</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/40 font-medium">
                            @forelse($customers as $c)
                                <tr class="hover:bg-slate-700/30">
                                    <td class="p-3 font-bold text-white">{{ $c->name }}</td>
                                    <td class="p-3 font-mono text-slate-400">{{ $c->phone ?? '-' }}</td>
                                    <td class="p-3 text-left font-mono font-black {{ (float)$c->current_balance > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                                        {{ number_format((float) $c->current_balance, 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-6 text-center text-slate-500">هیچ کڕیارێک تۆمار نەکراوە.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- لیستی دابینکەران -->
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-700/80 flex justify-between items-center bg-slate-800">
                    <h2 class="font-bold text-sm text-white">حیسابی کۆمپانیا و دابینکەران</h2>
                    <span class="text-xs text-rose-400 font-bold">قەرزدارین</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-900/60 text-slate-400 font-bold border-b border-slate-700/50">
                            <tr>
                                <th class="p-3">ناوی کۆمپانیا</th>
                                <th class="p-3">ژمارەی مۆبایل</th>
                                <th class="p-3 text-left">بڕی قەرز ({{ $settings['currency_symbol'] ?? 'د.ع' }})</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/40 font-medium">
                            @forelse($suppliers as $s)
                                <tr class="hover:bg-slate-700/30">
                                    <td class="p-3 font-bold text-white">{{ $s->name }}</td>
                                    <td class="p-3 font-mono text-slate-400">{{ $s->phone ?? '-' }}</td>
                                    <td class="p-3 text-left font-mono font-black text-rose-400">
                                        {{ number_format((float) $s->current_balance, 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-6 text-center text-slate-500">هیچ دابینکەرێک تۆمار نەکراوە.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <!-- مۆداڵی زیادکردنی کڕیار / دابینکەر -->
    <div id="modal-add-party" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">تۆمارکردنی کەس / کۆمپانیا</h3>
                <button onclick="document.getElementById('modal-add-party').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.debts.party.store') }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ناو:</label>
                    <input type="text" name="name" required placeholder="وەک: کاک ئارام یان کۆمپانیای ئەلبان" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">ژمارەی مۆبایل:</label>
                        <input type="text" name="phone" placeholder="07701234567" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">جۆر:</label>
                        <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            <option value="customer">کڕیار (Customer)</option>
                            <option value="supplier">دابینکەر (Supplier)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">قەرزی سەرەتایی ({{ $settings['currency_symbol'] ?? 'د.ع' }}):</label>
                        <input type="number" step="250" name="opening_balance" placeholder="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">سنووری قەرز (Limit):</label>
                        <input type="number" step="1000" name="credit_limit" placeholder="500000" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none">
                    </div>
                </div>
                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردن</button>
                    <button type="button" onclick="document.getElementById('modal-add-party').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مۆداڵی وەرگرتنەوە یان پێدانی پارە -->
    <div id="modal-add-payment" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 max-w-md w-full rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="font-bold text-sm text-white">وەرگرتنەوە یان پێدانی پارەی قەرز</h3>
                <button onclick="document.getElementById('modal-add-payment').classList.add('hidden')" class="text-slate-400 hover:text-white font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.debts.payment.store') }}" method="POST" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-300 font-bold mb-1">کەس / کۆمپانیا هەڵبژێرە:</label>
                    <select name="party_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                        <optgroup label="کڕیاران">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (قەرز: {{ number_format((float)$c->current_balance, 0) }} {{ $settings['currency_symbol'] ?? 'د.ع' }})</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="دابینکەران">
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} (قەرز: {{ number_format((float)$s->current_balance, 0) }} {{ $settings['currency_symbol'] ?? 'د.ع' }})</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">جۆری کردار:</label>
                        <select name="payment_type" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none">
                            <option value="receipt">وەرگرتنەوە لە کڕیار</option>
                            <option value="payout">پێدان بە دابینکەر</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">بڕی پارە ({{ $settings['currency_symbol'] ?? 'د.ع' }}):</label>
                        <input type="number" step="250" name="amount" required placeholder="25000" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-amber-500">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">تێبینی:</label>
                    <input type="text" name="notes" placeholder="وەک: بەشێک لە پارەی پسوولەی ژمارە..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none">
                </div>
                <div class="pt-3 flex gap-2">
                    <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-500 text-white font-bold py-3 rounded-xl transition">تۆمارکردنی وەسڵ</button>
                    <button type="button" onclick="document.getElementById('modal-add-payment').classList.add('hidden')" class="bg-slate-700 text-slate-300 px-4 py-3 rounded-xl">داخستن</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
