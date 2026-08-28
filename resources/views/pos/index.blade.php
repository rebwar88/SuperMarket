<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سندوقی فرۆشتن - POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> 
        * { font-family: 'Vazirmatn', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col select-none">

    <!-- سەرپەڕەی سندوق -->
    <header class="bg-slate-900 border-b border-slate-800 px-4 py-2.5 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-lg text-slate-950 shadow-lg shadow-emerald-500/20">S</div>
            <div>
                <h1 class="font-extrabold text-sm text-white flex items-center gap-2">
                    <span>سیستەمی سندوق (POS)</span>
                    <span id="system-time" class="text-[10px] text-slate-400 font-mono font-normal"></span>
                </h1>
                <p class="text-[10px] text-emerald-400">سیستەم ئامادەیە بۆ فرۆشتن</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="hidden sm:flex flex-col text-left pl-2">
                <span class="text-xs font-bold text-white">{{ auth()->user()->name ?? 'کاشێر' }}</span>
                <span class="text-[10px] text-emerald-400 font-mono">{{ auth()->user()->username ?? 'cashier' }}</span>
            </div>

            <!-- دوگمەی داخستنی شیفت -->
            <button type="button" onclick="openCloseShiftModal()" class="bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white border border-amber-500/30 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>داخستنی شیفت (Z)</span>
            </button>

            <!-- دوگمەی دەرچوونی کاتی -->
            <button type="button" onclick="openTempLogoutModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>دەرچوونی کاتی</span>
            </button>
        </div>
    </header>

    <!-- ناوەڕۆکی سندوق -->
    <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-4 p-4 overflow-hidden">
        
        <!-- بەشی گەڕان و بارکۆد و کاڵاکان -->
        <div class="lg:col-span-8 flex flex-col gap-4">
            <div class="bg-slate-900 border border-slate-800 p-3 rounded-2xl flex items-center gap-3">
                <input type="text" id="barcode-input" placeholder="بارکۆد لێبدە یان گەڕان بەپێی ناوی کاڵا..." autofocus class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500 font-mono">
            </div>

            <div class="flex-1 bg-slate-900 border border-slate-800 rounded-2xl p-4 overflow-y-auto">
                <div class="text-xs text-slate-400 mb-3 font-bold">لیستی کاڵا خێراکان:</div>
                <div id="quick-products-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <!-- کاڵاکان لێرەدا دەردەکەون -->
                </div>
            </div>
        </div>

        <!-- بەشی سەبەتە و پارەدان -->
        <div class="lg:col-span-4 bg-slate-900 border border-slate-800 rounded-2xl flex flex-col p-4">
            <div class="border-b border-slate-800 pb-3 mb-3 flex justify-between items-center">
                <h2 class="font-extrabold text-sm text-white">سەبەتەی فرۆشتن</h2>
                <button onclick="clearCart()" class="text-[11px] text-rose-400 hover:underline">بەتاڵکردنەوە</button>
            </div>

            <div id="cart-items" class="flex-1 overflow-y-auto divide-y divide-slate-800 space-y-2 mb-4 pr-1">
                <div id="empty-cart-msg" class="text-center text-slate-500 text-xs py-12">سەبەتە بەتاڵە</div>
            </div>

            <div class="border-t border-slate-800 pt-3 space-y-2 text-xs">
                <div class="flex justify-between text-slate-300 font-bold">
                    <span>کۆی گشتی:</span>
                    <span id="cart-grand-total" class="font-mono text-emerald-400 text-lg font-black">0 د.ع</span>
                </div>
                <button onclick="checkoutCash()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition text-sm shadow-lg shadow-emerald-600/20">
                    پارەدان بە کاش (Cash Out)
                </button>
            </div>
        </div>

    </main>

    <!-- مۆداڵی دەرچوونی کاتی -->
    <div id="modal-temp-logout" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 max-w-sm w-full rounded-3xl p-6 shadow-2xl space-y-4 text-right" dir="rtl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl font-bold">⚠️</div>
                <div>
                    <h3 class="font-extrabold text-sm text-white">دەرچوونی کاتی</h3>
                    <p class="text-[11px] text-slate-400">شیفتەکەت بە کراوەیی دەمێنێتەوە</p>
                </div>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                ئەم بژاردەیە تەنها بۆ پشوو یان بەجێهێشتنی کاتیی سندوقەکەیە. کاتێک دەگەڕێیتەوە هەمان شیفت کراوە دەبێت. دڵنیایت لە دەرچوون؟
            </p>
            <div class="pt-2 flex gap-2">
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 rounded-xl text-xs border border-slate-600 transition">بەڵێ، دەربچۆ</button>
                </form>
                <button type="button" onclick="closeTempLogoutModal()" class="flex-1 bg-slate-950 hover:bg-slate-800 text-slate-400 font-bold py-2.5 rounded-xl text-xs border border-slate-800 transition">پاشگەزبوونەوە</button>
            </div>
        </div>
    </div>

    <!-- مۆداڵی داخستنی شیفت و حیساباتی سندوق -->
    <div id="modal-close-shift" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 max-w-md w-full rounded-3xl p-6 shadow-2xl space-y-4 text-right" dir="rtl">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <div>
                    <h3 class="font-extrabold text-sm text-white">داخستنی شیفت و چاپی Z-Report</h3>
                    <p class="text-[11px] text-slate-400">ژماردنی کاش و کۆتاییهێنان بە دەوام</p>
                </div>
                <button onclick="closeShiftModal()" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <div class="bg-slate-950/80 p-4 rounded-2xl border border-slate-800 space-y-2 text-xs font-medium">
                <div class="flex justify-between text-slate-300">
                    <span>کاشی سەرەتایی سندوق:</span>
                    <span id="shift-open-cash" class="font-mono font-bold text-white">0 د.ع</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>کۆی فرۆش بە کاش لەم شیفتە:</span>
                    <span id="shift-cash-sales" class="font-mono font-bold text-emerald-400">0 د.ع</span>
                </div>
                <div class="border-t border-slate-800 pt-2 flex justify-between text-slate-200 font-bold">
                    <span>کاشی چاوەڕوانکراو لە ناو سندوق:</span>
                    <span id="shift-expected-cash" class="font-mono text-emerald-400 font-black">0 د.ع</span>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">بڕی کاشی ژمێردراوی ناو سندوق (د.ع):</label>
                    <input type="number" id="input-actual-cash" step="250" placeholder="بڕی کاش بنووسە..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white font-mono text-sm font-bold outline-none focus:border-emerald-500">
                </div>

                <div id="diff-box" class="hidden p-3 rounded-xl text-xs font-bold font-mono"></div>
                <div id="error-msg-box" class="hidden p-3 rounded-xl text-xs font-bold bg-rose-500/10 border border-rose-500/30 text-rose-400"></div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">تێبینی (ئارەزوومەندانە):</label>
                    <textarea id="shift-notes" rows="2" placeholder="هۆکاری کورتهێنان یان زیادی..." class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-white text-xs outline-none focus:border-emerald-500"></textarea>
                </div>
            </div>

            <div class="pt-2 flex gap-2.5">
                <button onclick="submitCloseShift()" id="btn-submit-close-shift" class="flex-1 bg-amber-600 hover:bg-amber-500 text-white font-bold py-3 rounded-xl transition text-xs shadow-lg shadow-amber-600/20 flex items-center justify-center gap-2">
                    <span>داخستنی شیفت و تەواوکردن</span>
                </button>
                <button type="button" onclick="closeShiftModal()" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl text-xs font-bold">داخستن</button>
            </div>
        </div>
    </div>

    <!-- سکریپتی سەرەکی سندوق و مۆداڵەکان -->
    <script>
        function updateClock() {
            const timeEl = document.getElementById('system-time');
            if (timeEl) {
                const now = new Date();
                timeEl.innerText = now.toLocaleTimeString('en-GB');
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        let expectedCashAmount = 0;

        function openTempLogoutModal() {
            document.getElementById('modal-temp-logout').classList.remove('hidden');
        }

        function closeTempLogoutModal() {
            document.getElementById('modal-temp-logout').classList.add('hidden');
        }

        function closeShiftModal() {
            document.getElementById('modal-close-shift').classList.add('hidden');
        }

        function openCloseShiftModal() {
            fetch("{{ route('shift.current') }}")
                .then(res => res.json())
                .then(data => {
                    if (!data.success && data.message) {
                        alert(data.message);
                        return;
                    }
                    expectedCashAmount = data.expected_cash;
                    document.getElementById('shift-open-cash').innerText = Number(data.opening_cash).toLocaleString() + ' د.ع';
                    document.getElementById('shift-cash-sales').innerText = Number(data.cash_sales).toLocaleString() + ' د.ع';
                    document.getElementById('shift-expected-cash').innerText = Number(data.expected_cash).toLocaleString() + ' د.ع';
                    document.getElementById('input-actual-cash').value = '';
                    document.getElementById('diff-box').classList.add('hidden');
                    document.getElementById('error-msg-box').classList.add('hidden');
                    document.getElementById('modal-close-shift').classList.remove('hidden');
                })
                .catch(err => {
                    console.error("Shift Fetch Error:", err);
                });
        }

        const inputActual = document.getElementById('input-actual-cash');
        if (inputActual) {
            inputActual.addEventListener('input', function() {
                const val = parseFloat(this.value);
                const box = document.getElementById('diff-box');
                
                if (isNaN(val)) {
                    box.classList.add('hidden');
                    return;
                }

                const diff = val - expectedCashAmount;
                box.classList.remove('hidden');

                if (diff < 0) {
                    box.className = 'p-3 rounded-xl text-xs font-bold font-mono bg-rose-500/10 border border-rose-500/30 text-rose-400';
                    box.innerText = 'کورتهێنان لە سندوق: ' + Number(Math.abs(diff)).toLocaleString() + ' د.ع ⚠️';
                } else if (diff > 0) {
                    box.className = 'p-3 rounded-xl text-xs font-bold font-mono bg-amber-500/10 border border-amber-500/30 text-amber-400';
                    box.innerText = 'زیادە لە سندوق: +' + Number(diff).toLocaleString() + ' د.ع';
                } else {
                    box.className = 'p-3 rounded-xl text-xs font-bold font-mono bg-emerald-500/10 border border-emerald-500/30 text-emerald-400';
                    box.innerText = 'حیساب بە تەواوی ڕێک و دروستە ✓';
                }
            });
        }

        function submitCloseShift() {
            const actual = document.getElementById('input-actual-cash').value;
            const errBox = document.getElementById('error-msg-box');
            
            if (actual === '' || isNaN(actual)) {
                errBox.innerText = 'تکایە بڕی کاشی ژمێردراوی ناو سندوق بنووسە.';
                errBox.classList.remove('hidden');
                return;
            }

            errBox.classList.add('hidden');
            const notes = document.getElementById('shift-notes').value;
            const btn = document.getElementById('btn-submit-close-shift');
            btn.disabled = true;
            btn.innerText = 'داخستن و تەواوکردنی شیفت...';

            fetch("{{ route('shift.close') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    actual_cash: parseFloat(actual),
                    notes: notes
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.open(data.z_report_url, '_blank');
                    window.location.href = "{{ route('login') }}";
                } else {
                    throw new Error(data.message || 'هەڵەیەک ڕوویدا');
                }
            })
            .catch(err => {
                errBox.innerText = err.message || 'هەڵەیەک ڕوویدا لە کاتی داخستنی شیفت.';
                errBox.classList.remove('hidden');
                btn.disabled = false;
                btn.innerText = 'داخستنی شیفت و تەواوکردن';
            });
        }

        function clearCart() {}
        function checkoutCash() {}

        // پشکنینی ڕاستەوخۆی سێشن
        setInterval(function() {
            fetch("{{ route('auth.session.ping') }}", {
                headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
            })
            .then(function(res) {
                if (res.status === 401 || res.status === 419 || res.redirected) {
                    window.location.href = "{{ route('login') }}";
                }
            })
            .catch(function() {});
        }, 5000);
    </script>
</body>
</html>
