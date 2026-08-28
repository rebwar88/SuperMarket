<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چوونەژوورەوە - سیستەمی سوپەرمارکێت</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> * { font-family: 'Vazirmatn', sans-serif; } </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>

        <div class="flex flex-col items-center mb-6 relative">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500 flex items-center justify-center font-black text-2xl text-slate-950 shadow-xl shadow-emerald-500/20 mb-3">
                S
            </div>
            <h1 class="text-lg font-black text-white">چوونەژوورەوە بۆ سیستەم</h1>
            <p class="text-xs text-slate-400 mt-1">نازناو (Username) یان ئیمەیڵ بنووسە</p>
        </div>

        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-3.5 rounded-xl text-xs font-semibold mb-4 space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 font-bold mb-1.5">ناوی بەکارهێنەر یان ئیمەیڵ:</label>
                <input type="text" name="login" value="{{ old('login') }}" required autofocus placeholder="وەک: cashier1 یان user@market.com" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white text-xs font-mono outline-none focus:border-emerald-500 transition">
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1.5">وشەی نهێنی (Password):</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white text-xs font-mono outline-none focus:border-emerald-500 transition">
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-emerald-500">
                    <span class="text-[11px]">لەبیرم مەکە</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-emerald-600/20 text-xs">
                چوونەژوورەوە
            </button>
        </form>
    </div>

</body>
</html>
