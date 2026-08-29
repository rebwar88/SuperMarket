<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'داشبۆرد') - {{ $settings['market_name'] ?? 'سیستەمی مارکێت' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Vazirmatn', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
    @yield('styles')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between">

    @php
        $user = auth()->user();
        $isCashier = false;
        if ($user) {
            $roleName = \Illuminate\Support\Facades\DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_uuid', $user->id)
                ->value('roles.name');
            $isCashier = in_array(strtolower((string)$roleName), ['cashier', 'کاشێر'], true);
        }
    @endphp

    @if(!$isCashier)
    <!-- هێدەری سەرەکی تەنها بۆ ئەو ڕۆڵانەی کە کاشێر نین و دەسەڵاتیان هەیە -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ url('/dashboard') }}" class="flex items-center gap-2 font-extrabold text-white text-base">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-sm font-black">🛒</span>
                    <span>{{ $settings['market_name'] ?? 'سیستەمی مارکێت' }}</span>
                </a>
            </div>

            <nav class="hidden md:flex items-center gap-1 text-xs font-bold text-slate-300">
                <a href="{{ url('/dashboard') }}" class="px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition">داشبۆرد</a>
                <a href="{{ url('/pos') }}" class="px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition">سندوق و فرۆشتن (POS)</a>
                <a href="{{ url('/inventory') }}" class="px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition">کۆگا و کاڵاکان</a>
                <a href="{{ url('/debts') }}" class="px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition">قەرزەکان</a>
                <a href="{{ url('/expenses') }}" class="px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition">خەرجی</a>
                <a href="{{ url('/reports/z-report') }}" class="px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition">ڕاپۆرتەکان</a>
                <a href="{{ url('/access-control') }}" class="px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition">کارمەندان و دەسەڵات</a>
                <a href="{{ url('/settings') }}" class="px-3 py-2 rounded-xl hover:bg-slate-800 hover:text-white transition">ڕێکخستنەکان</a>
            </nav>

            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-300 font-bold bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-700">
                    👤 {{ auth()->user()->name ?? 'بەکارهێنەر' }}
                </span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 text-rose-400 hover:bg-rose-950/40 rounded-xl transition border border-rose-500/20 text-xs font-bold" title="چوونەدەرەوە">
                        دەرچوون
                    </button>
                </form>
            </div>
        </div>
    </header>
    @endif

    <!-- ناوەڕۆکی شاشە -->
    <main class="{{ $isCashier ? 'w-full min-h-screen' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-1 w-full space-y-6' }}">
        @yield('content')
    </main>

    @if(!$isCashier)
    <footer class="bg-slate-900 border-t border-slate-800 py-4 text-center text-xs text-slate-500">
        {{ $settings['market_name'] ?? 'سیستەمی مارکێت' }} &copy; {{ date('Y') }} - هەموو مافەکان پارێزراون.
    </footer>
    @endif

    @yield('scripts')
</body>
</html>
