<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چوونەژوورەوە - {{ $settings['market_name'] ?? 'سوپەرمارکێت' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Vazirmatn', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-slate-900 border border-slate-800 w-full max-w-md p-8 rounded-3xl shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 bg-emerald-500 rounded-2xl mx-auto flex items-center justify-center text-slate-950 text-2xl font-black shadow-lg shadow-emerald-500/20">
                S
            </div>
            <h1 class="text-xl font-extrabold text-white">چوونەژوورەوە بۆ سیستەم</h1>
            <p class="text-xs text-slate-400">نازناو (Username) یان ئیمەیڵ بنووسە</p>
        </div>

        @if($errors->any())
            <div class="bg-rose-950/60 border border-rose-500/40 text-rose-300 p-3.5 rounded-2xl text-xs font-bold space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block text-slate-300 font-bold mb-1.5">ناوی بەکارهێنەر یان ئیمەیڵ:</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="بۆ نموونە: rebwar یان milad" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white font-mono outline-none focus:border-emerald-500 transition">
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1.5">وشەی نهێنی (Password):</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white font-mono outline-none focus:border-emerald-500 transition">
            </div>

            <div class="flex items-center justify-end">
                <label class="flex items-center gap-2 cursor-pointer text-slate-400 select-none">
                    <span>لەبیرم مەکە</span>
                    <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-800 text-emerald-500">
                </label>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-extrabold py-3.5 rounded-xl transition shadow-lg shadow-emerald-600/20 text-sm">
                چوونەژوورەوە
            </button>
        </form>
    </div>

</body>
</html>
