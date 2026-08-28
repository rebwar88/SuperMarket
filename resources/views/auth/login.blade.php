<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چوونەژوورەوە - سیستەمی سندوق و مارکێت</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 selection:bg-emerald-500 selection:text-white">

    <div class="max-w-md w-full">
        <!-- لۆگۆ و ناونیشان -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-emerald-500 items-center justify-center font-black text-3xl text-slate-950 shadow-lg shadow-emerald-500/20 mb-3">
                S
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">سیستەمی سوپەرمارکێت</h1>
            <p class="text-sm text-slate-400 mt-1">تکایە هەژماری کاشێر یان بەڕێوەبەر داخڵ بکە</p>
        </div>

        <!-- فۆرمی چوونەژوورەوە -->
        <div class="bg-slate-800/80 backdrop-blur border border-slate-700/80 rounded-2xl p-7 shadow-2xl">
            @if ($errors->any())
                <div class="mb-5 bg-rose-500/10 border border-rose-500/30 rounded-xl p-3.5 text-rose-400 text-xs font-semibold">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">ناوی بەکارهێنەر (Username):</label>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                        placeholder="cashier"
                        class="w-full bg-slate-900/90 border border-slate-700 focus:border-emerald-500 rounded-xl px-4 py-3 text-sm text-white placeholder:text-slate-500 outline-none transition duration-200" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">وشەی نهێنی (Password):</label>
                    <input type="password" name="password" required autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full bg-slate-900/90 border border-slate-700 focus:border-emerald-500 rounded-xl px-4 py-3 text-sm text-white placeholder:text-slate-500 outline-none transition duration-200 font-mono" />
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-emerald-500 focus:ring-0 focus:ring-offset-0" />
                        <span>لەبیرم مەبە</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-600/25 transition duration-200 text-sm">
                    چوونەژوورەوە
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-700/50 text-center text-xs text-slate-500">
                هەژماری سێدەری کاشێر: <span class="text-slate-300 font-mono">cashier</span> | نهێنی: <span class="text-slate-300 font-mono">password123</span>
            </div>
        </div>
    </div>

</body>
</html>
