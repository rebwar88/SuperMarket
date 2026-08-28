<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>سیستەمی سندوق و کاشێر - SuperMarket POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        /* شێوازی چاپی پسوولەی گەرمی (Thermal 80mm Receipt Styling) */
        @media print {
            body * { visibility: hidden; }
            #thermal-receipt, #thermal-receipt * { visibility: visible; }
            #thermal-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 78mm;
                margin: 0;
                padding: 4mm;
                color: #000;
                background: #fff;
                display: block !important;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 h-screen overflow-hidden flex flex-col select-none">

    <!-- سەرپەڕەی سەرەکی (Header) -->
    <header class="bg-slate-900 text-white px-5 py-2.5 flex items-center justify-between shadow-md border-b border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-500 flex items-center justify-center font-black text-xl text-slate-950">S</div>
            <div>
                <h1 class="font-bold text-base leading-tight">سیستەمی فرۆشتن و کاشێر</h1>
                <p class="text-xs text-slate-400">سندوقی ژمارە: <span class="text-emerald-400 font-semibold">REG-01</span></p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 bg-slate-800/80 px-3 py-1.5 rounded-lg border border-slate-700">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs text-slate-300 font-medium">شیفتی بەردەست</span>
            </div>
            <div id="live-clock" class="text-sm font-semibold tracking-wider text-slate-200">00:00:00</div>
        </div>
    </header>

    <!-- ناوەڕۆک -->
    <main class="flex-1 flex overflow-hidden p-3 gap-3">

        <!-- سەبەتەی کڕین -->
        <section class="flex-1 bg-white rounded-xl shadow-sm border border-slate-200/80 flex flex-col overflow-hidden">
            
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center gap-3">
                <div class="relative flex-1">
                    <input type="text" id="barcode-input" autofocus autocomplete="off"
                        placeholder="بارکۆد لێرە سکان بکە یان بنووسە... (ئینتەر دابگرە)"
                        class="w-full bg-white border-2 border-emerald-500/80 focus:border-emerald-600 rounded-lg px-4 py-2.5 text-base font-semibold outline-none transition shadow-sm text-slate-900 placeholder:text-slate-400" />
                    <span class="absolute left-3 top-2.5 text-xs bg-slate-200 text-slate-600 px-2 py-1 rounded font-mono">Enter</span>
                </div>
            </div>

            <div class="grid grid-cols-12 bg-slate-100/80 px-4 py-2 text-xs font-bold text-slate-600 border-b border-slate-200">
                <div class="col-span-1 text-center">#</div>
                <div class="col-span-5">ناوی کاڵا</div>
                <div class="col-span-2 text-center">بڕ / دانە</div>
                <div class="col-span-2 text-left">نرخ (د.ع)</div>
                <div class="col-span-2 text-left">کۆ (د.ع)</div>
            </div>

            <div id="cart-items" class="flex-1 overflow-y-auto custom-scroll divide-y divide-slate-100"></div>

            <div class="bg-slate-50 px-4 py-2 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500 font-medium">
                <div class="flex items-center gap-3">
                    <span><kbd class="bg-slate-200 px-1.5 py-0.5 rounded font-mono font-bold text-slate-700">F1</kbd> ڕاگرتن</span>
                    <span><kbd class="bg-slate-200 px-1.5 py-0.5 rounded font-mono font-bold text-slate-700">F8</kbd> پاککردنەوە</span>
                </div>
                <div>
                    <span><kbd class="bg-emerald-600 text-white px-2 py-0.5 rounded font-mono font-bold">F4</kbd> پارەدان و چاپ</span>
                </div>
            </div>
        </section>

        <!-- حیسابات -->
        <aside class="w-96 bg-white rounded-xl shadow-sm border border-slate-200/80 flex flex-col justify-between p-4">
            <div class="space-y-4">
                <h2 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">پوختەی پسوولە</h2>

                <div class="space-y-2.5 bg-slate-50 p-3 rounded-lg border border-slate-100">
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>کۆی کاڵاکان:</span>
                        <span id="summary-subtotal" class="font-semibold text-slate-800">0 د.ع</span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>داشکاندن:</span>
                        <span id="summary-discount" class="font-semibold text-rose-600">0 د.ع</span>
                    </div>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">کۆی کۆتایی</span>
                    <div id="summary-grand-total" class="text-3xl font-extrabold text-emerald-700 mt-1 font-mono">0</div>
                    <span class="text-xs text-emerald-600 font-semibold">دیناری عێراقی</span>
                </div>
            </div>

            <div class="space-y-2 pt-4 border-t border-slate-100">
                <button onclick="openCheckoutModal()" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition flex items-center justify-center gap-2 text-base">
                    <span>پارەدان و چاپی پسوولە (F4)</span>
                </button>
            </div>
        </aside>
    </main>

    <!-- مۆداڵی پارەدان -->
    <div id="checkout-modal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
            <div class="bg-slate-900 text-white px-5 py-3.5 flex justify-between items-center">
                <h3 class="font-bold text-base">تەواوکردنی فرۆشتن و بڕینی پسوولە</h3>
                <button onclick="closeCheckoutModal()" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>
            
            <div class="p-5 space-y-4">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-center">
                    <span class="text-xs text-slate-500 font-medium">کۆی گشتی بۆ دان</span>
                    <div id="modal-grand-total" class="text-2xl font-black text-slate-900 font-mono">0 د.ع</div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">پارەی وەرگیراو لە کڕیار:</label>
                    <input type="number" id="paid-amount-input" step="250"
                        class="w-full text-center text-xl font-bold bg-white border-2 border-slate-300 focus:border-emerald-600 rounded-xl py-2.5 outline-none font-mono"
                        placeholder="0" oninput="calculateChange()" />
                </div>

                <div class="bg-amber-50 border border-amber-200 p-3 rounded-xl flex justify-between items-center">
                    <span class="text-xs font-bold text-amber-900">بڕی پارەی گەڕاوە:</span>
                    <span id="change-due-display" class="text-lg font-black text-amber-800 font-mono">0 د.ع</span>
                </div>

                <div class="pt-2 flex gap-2">
                    <button onclick="submitCheckout()" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl shadow-md transition text-sm">
                        چاپکردن و کۆتایی (Enter)
                    </button>
                    <button onclick="closeCheckoutModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-3 rounded-xl text-sm transition">
                        پاشگەزبوونەوە
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- پێکهاتەی پەڕەی چاپی پسوولە (Thermal Print Template) -->
    <div id="thermal-receipt" class="hidden text-xs leading-tight font-mono text-black">
        <div class="text-center pb-2 border-b border-dashed border-black">
            <h2 class="font-bold text-base">سوپەرمارکێتی کوردی</h2>
            <p>پسوولەی کڕین</p>
            <p id="rcpt-invoice" class="font-bold">INV-000000</p>
            <p id="rcpt-date">2026-08-27 00:00</p>
        </div>

        <table class="w-full my-2 border-b border-dashed border-black text-right">
            <thead>
                <tr class="border-b border-black">
                    <th class="py-1">کاڵا</th>
                    <th class="text-center">بڕ</th>
                    <th class="text-left">کۆ</th>
                </tr>
            </thead>
            <tbody id="rcpt-items-body"></tbody>
        </table>

        <div class="space-y-1 text-left pb-2 border-b border-dashed border-black">
            <div class="flex justify-between font-bold text-sm">
                <span>کۆی گشتی:</span>
                <span id="rcpt-grand-total">0 IQD</span>
            </div>
            <div class="flex justify-between">
                <span>پارەی وەرگیراو:</span>
                <span id="rcpt-paid">0 IQD</span>
            </div>
            <div class="flex justify-between font-semibold">
                <span>گەڕاوە:</span>
                <span id="rcpt-change">0 IQD</span>
            </div>
        </div>

        <div class="text-center pt-2">
            <p>سوپاس بۆ سەردانەکەت!</p>
            <p class="text-[10px]">کاتی گەڕاندنەوە لە ماوەی ٢٤ کاتژمێردایە</p>
        </div>
    </div>

    <!-- ئاگاداری Toast -->
    <div id="toast-container" class="fixed bottom-5 left-5 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <script>
        let cart = [];
        const barcodeInput = document.getElementById('barcode-input');

        setInterval(() => {
            document.getElementById('live-clock').innerText = new Date().toLocaleTimeString('en-GB');
        }, 1000);

        window.addEventListener('keydown', (e) => {
            if (e.key === 'F4') { e.preventDefault(); openCheckoutModal(); }
            if (e.key === 'F8') { e.preventDefault(); clearCart(); }
            if (e.key === 'Escape') { closeCheckoutModal(); }
        });

        barcodeInput.addEventListener('keydown', async (e) => {
            if (e.key === 'Enter') {
                const barcode = barcodeInput.value.trim();
                if (barcode) {
                    await handleScan(barcode);
                    barcodeInput.value = '';
                }
            }
        });

        async function handleScan(barcode) {
            try {
                const res = await fetch('/api/v1/pos/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ barcode: barcode })
                });

                const json = await res.json();
                if (!res.ok || !json.success) {
                    showToast(json.message || 'هیچ کاڵایەک نەدۆزرایەوە!', 'error');
                    return;
                }

                addToCart(json.data);
                showToast(`کاڵای (${json.data.name}) زیادکرا`, 'success');
            } catch (err) {
                showToast('هەڵەیەک لە پەیوەندی بە سێرڤەر ڕوویدا.', 'error');
            }
        }

        function addToCart(item) {
            const index = cart.findIndex(i => i.product_id === item.product_id && i.unit_id === item.unit_id);
            if (index > -1) {
                cart[index].quantity += (item.quantity || 1);
                cart[index].total_price = cart[index].quantity * cart[index].unit_price;
            } else {
                cart.push({
                    product_id: item.product_id,
                    unit_id: item.unit_id,
                    name: item.name,
                    quantity: item.quantity || 1,
                    unit_price: item.unit_price,
                    total_price: (item.quantity || 1) * item.unit_price
                });
            }
            renderCart();
        }

        function updateQty(index, delta) {
            cart[index].quantity += delta;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            } else {
                cart[index].total_price = cart[index].quantity * cart[index].unit_price;
            }
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            container.innerHTML = '';
            let subtotal = 0;

            cart.forEach((item, index) => {
                subtotal += item.total_price;
                const row = document.createElement('div');
                row.className = 'grid grid-cols-12 px-4 py-3 items-center hover:bg-slate-50 border-b border-slate-100 text-sm';
                row.innerHTML = `
                    <div class="col-span-1 text-center font-mono text-slate-400 font-bold">${index + 1}</div>
                    <div class="col-span-5 font-bold text-slate-800">${item.name}</div>
                    <div class="col-span-2 flex items-center justify-center gap-1.5">
                        <button onclick="updateQty(${index}, -1)" class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 font-bold">-</button>
                        <span class="font-mono font-bold px-2">${item.quantity}</span>
                        <button onclick="updateQty(${index}, 1)" class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 font-bold">+</button>
                    </div>
                    <div class="col-span-2 text-left font-mono font-semibold">${Number(item.unit_price).toLocaleString()}</div>
                    <div class="col-span-2 text-left font-mono font-bold text-emerald-700">${Number(item.total_price).toLocaleString()}</div>
                `;
                container.appendChild(row);
            });

            document.getElementById('summary-subtotal').innerText = subtotal.toLocaleString() + ' د.ع';
            document.getElementById('summary-grand-total').innerText = subtotal.toLocaleString();
            document.getElementById('modal-grand-total').innerText = subtotal.toLocaleString() + ' د.ع';
        }

        function clearCart() {
            cart = [];
            renderCart();
            showToast('سەبەتە پاککرایەوە.', 'info');
        }

        function openCheckoutModal() {
            if (cart.length === 0) {
                showToast('سەبەتەی کڕین بەتاڵە!', 'error');
                return;
            }
            const total = cart.reduce((sum, item) => sum + item.total_price, 0);
            document.getElementById('paid-amount-input').value = total;
            calculateChange();
            document.getElementById('checkout-modal').classList.remove('hidden');
            setTimeout(() => document.getElementById('paid-amount-input').focus(), 50);
        }

        function closeCheckoutModal() {
            document.getElementById('checkout-modal').classList.add('hidden');
            barcodeInput.focus();
        }

        function calculateChange() {
            const total = cart.reduce((sum, item) => sum + item.total_price, 0);
            const paid = parseFloat(document.getElementById('paid-amount-input').value) || 0;
            const change = paid - total;
            document.getElementById('change-due-display').innerText = (change >= 0 ? change : 0).toLocaleString() + ' د.ع';
        }

        async function submitCheckout() {
            const total = cart.reduce((sum, item) => sum + item.total_price, 0);
            const paid = parseFloat(document.getElementById('paid-amount-input').value) || 0;

            if (paid < total) {
                showToast('پارەی دراو کەمترە لە کۆی پسوولەکە!', 'error');
                return;
            }

            // ئامادەکردنی زانیارییەکانی پسوولە بۆ چاپ
            document.getElementById('rcpt-invoice').innerText = 'INV-' + Math.floor(100000 + Math.random() * 900000);
            document.getElementById('rcpt-date').innerText = new Date().toLocaleString('en-GB');
            document.getElementById('rcpt-grand-total').innerText = total.toLocaleString() + ' IQD';
            document.getElementById('rcpt-paid').innerText = paid.toLocaleString() + ' IQD';
            document.getElementById('rcpt-change').innerText = (paid - total).toLocaleString() + ' IQD';

            const tbody = document.getElementById('rcpt-items-body');
            tbody.innerHTML = '';
            cart.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="py-1">${item.name}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-left">${item.total_price.toLocaleString()}</td>
                `;
                tbody.appendChild(tr);
            });

            // ئەنجامدانی چاپی خێرای گەرمی
            window.print();

            showToast('پسوولە بە سەرکەوتوویی بڕدرا و چاپکرا.', 'success');
            closeCheckoutModal();
            cart = [];
            renderCart();
        }

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-emerald-600 text-white',
                error: 'bg-rose-600 text-white',
                info: 'bg-slate-800 text-white'
            };
            toast.className = `px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 text-sm font-semibold transition ${colors[type]}`;
            toast.innerText = message;
            container.appendChild(toast);
            setTimeout(() => { toast.remove(); }, 3000);
        }
    </script>
</body>
</html>
