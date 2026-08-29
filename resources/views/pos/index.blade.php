@extends('layouts.admin')

@section('title', 'سندوقی فرۆشتن (POS)')

@section('content')
<script>
    window.systemTimezone = "{{ $settings['timezone'] ?? config('app.timezone', 'Asia/Baghdad') }}";
    var systemTimezone = window.systemTimezone;
</script>


    <!-- سەرپەڕەی سندوق -->
    <header class="bg-slate-900 border-b border-slate-800 px-4 py-2 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center gap-3">
            @if(!empty($settings['market_logo']))
                <img src="{{ $settings['market_logo'] }}" alt="Logo" class="w-9 h-9 object-contain rounded-xl bg-white p-0.5 shadow-lg">
            @else
                <div class="w-9 h-9 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-lg text-slate-950 shadow-lg shadow-emerald-500/20">S</div>
            @endif
            <div>
                <h1 class="font-extrabold text-sm text-white flex items-center gap-2">
                    <span>{{ $settings['market_name'] ?? 'سیستەمی سندوق (POS)' }}</span>
                    <span id="system-time" class="text-[10px] text-slate-400 font-mono font-normal"></span>
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-1.5 overflow-x-auto max-w-md px-2 py-1 bg-slate-950/60 rounded-2xl border border-slate-800" id="tabs-container"></div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="addNewTab()" class="bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white border border-indigo-500/30 px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm" title="وەسڵی نوێ">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>وەسڵی نوێ</span>
            </button>

            <button type="button" onclick="openMyInvoicesModal()" class="bg-teal-600/20 hover:bg-teal-600 text-teal-300 hover:text-white border border-teal-500/30 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>وەسڵەکانم</span>
            </button>

            <button type="button" onclick="openCloseShiftModal()" class="bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white border border-amber-500/30 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>داخستنی شیفت (Z)</span>
            </button>

            <button type="button" onclick="openTempLogoutModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>دەرچوونی کاتی</span>
            </button>
        </div>
    </header>

    <!-- ناوەڕۆک -->
    <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-4 p-4 overflow-hidden">
        
        <div class="lg:col-span-8 flex flex-col gap-4">
            <div class="bg-slate-900 border border-slate-800 p-3 rounded-2xl flex items-center gap-3">
                <input type="text" id="barcode-input" placeholder="بارکۆد لێبدە یان ناوی کاڵا بنووسە..." autofocus class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500 font-mono">
            </div>

            <div class="flex-1 bg-slate-900 border border-slate-800 rounded-2xl p-4 overflow-y-auto">
                <div class="text-xs text-slate-400 mb-3 font-bold">لیستی کاڵاکان:</div>
                <div id="products-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"></div>
            </div>
        </div>

        <div class="lg:col-span-4 bg-slate-900 border border-slate-800 rounded-2xl flex flex-col p-4">
            <div class="border-b border-slate-800 pb-3 mb-3 flex justify-between items-center">
                <h2 class="font-extrabold text-sm text-white flex items-center gap-2">
                    <span>سەبەتەی فرۆشتن</span>
                    <span id="current-tab-label" class="bg-emerald-500/20 text-emerald-400 text-[10px] px-2 py-0.5 rounded-lg font-mono">وەسڵی ١</span>
                </h2>
                <button onclick="promptClearCart()" class="text-[11px] text-rose-400 hover:underline">بەتاڵکردنەوە</button>
            </div>

            <div id="cart-items" class="flex-1 overflow-y-auto divide-y divide-slate-800/80 space-y-2 mb-4 pr-1"></div>

            <div class="border-t border-slate-800 pt-3 space-y-2.5 text-xs">
                <div class="flex justify-between text-slate-300 font-bold">
                    <span>کۆی گشتی:</span>
                    <span id="cart-grand-total" class="font-mono text-emerald-400 text-lg font-black">0 {{ $settings['currency_symbol'] ?? 'د.ع' }}</span>
                </div>

                <div class="grid grid-cols-3 gap-2 pt-1">
                    <button onclick="handlePayAction('pay_now')" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-2.5 rounded-xl transition text-xs flex items-center justify-center shadow-lg shadow-emerald-600/20">
                        <span>پارەدان (کاش)</span>
                    </button>
                    <button onclick="handlePayAction('pay_online')" class="bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-2.5 rounded-xl transition text-xs flex items-center justify-center shadow-lg shadow-blue-600/20">
                        <span>ئۆنلاین</span>
                    </button>
                    @if(($settings['allow_pay_later'] ?? '1') === '1')
                    <button id="btn-pay-later" onclick="handlePayAction('pay_later')" class="bg-amber-600 hover:bg-amber-500 text-white font-extrabold py-2.5 rounded-xl transition text-xs flex items-center justify-center shadow-lg shadow-amber-600/20">
                        <span>قەرز</span>
                    </button>
                    @else
                    <button disabled class="bg-slate-800 text-slate-500 font-bold py-2.5 rounded-xl text-xs cursor-not-allowed opacity-50" title="فرۆشتنی قەرز ناچالاک کراوە">
                        <span>قەرز (قفڵە)</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>

    </main>

    <!-- سیستەمی ئاگاداری Toast -->
    <div id="toast-container" class="fixed bottom-5 left-5 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- مۆداڵی پرۆفیشناڵی دڵنیابوونەوە -->
    <div id="modal-confirm" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 transition-all">
        <div class="bg-slate-900 border border-slate-700 max-w-sm w-full rounded-3xl p-6 shadow-2xl space-y-4 text-right" dir="rtl">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl">⚠️</div>
                <div>
                    <h3 id="confirm-title" class="font-black text-sm text-white">دڵنیابوونەوە</h3>
                    <p id="confirm-subtitle" class="text-[11px] text-slate-400">تکایە پشتڕاستی بکەرەوە</p>
                </div>
            </div>
            <p id="confirm-message" class="text-xs text-slate-300 leading-relaxed"></p>
            <div class="flex gap-2 pt-2">
                <button id="confirm-btn-yes" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl text-xs transition">بەڵێ</button>
                <button id="confirm-btn-no" onclick="closeConfirmModal()" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 py-2.5 rounded-xl text-xs font-bold transition">پاشگەزبوونەوە</button>
            </div>
        </div>
    </div>

    <!-- مۆداڵی وەسڵەکانی کاشێر -->
    <div id="modal-my-invoices" class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-teal-500/40 max-w-2xl w-full rounded-3xl p-6 shadow-2xl space-y-4 text-right flex flex-col max-h-[85vh]" dir="rtl">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-teal-500/20 text-teal-400 flex items-center justify-center text-xl font-bold">🧾</div>
                    <div>
                        <h3 class="font-black text-sm text-white">وەسڵەکانی ئەم کاشێرە</h3>
                        <p class="text-[10px] text-slate-400">تەنها وەسڵەکانی خۆت پیشان دەدرێن</p>
                    </div>
                </div>
                <button onclick="closeMyInvoicesModal()" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <div id="my-invoices-list" class="flex-1 overflow-y-auto space-y-2 pr-1 text-xs">
                <div class="text-center text-slate-500 py-8">چاوەڕوان بە...</div>
            </div>

            <div class="border-t border-slate-800 pt-3 flex justify-end">
                <button type="button" onclick="closeMyInvoicesModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-5 py-2.5 rounded-xl text-xs font-bold transition">داخستن</button>
            </div>
        </div>
    </div>

    <!-- مۆداڵی تەواوکردنی وەسڵ -->
    <div id="modal-checkout" class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 max-w-md w-full rounded-3xl p-6 shadow-2xl space-y-4 text-right" dir="rtl">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">🖨️</div>
                    <div>
                        <h3 class="font-extrabold text-sm text-white">تەواوکردنی وەسڵ</h3>
                        <p id="checkout-method-label" class="text-[10px] text-slate-400 font-bold font-mono">شێواز: پارەدان</p>
                    </div>
                </div>
                <button onclick="closeCheckoutModal()" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <div class="bg-slate-950/80 p-3.5 rounded-2xl border border-slate-800 text-center space-y-1">
                <span class="text-xs text-slate-400 font-bold">کۆی گشتی:</span>
                <div id="checkout-total-display" class="font-mono text-2xl font-black text-emerald-400">0 {{ $settings['currency_symbol'] ?? 'د.ع' }}</div>
            </div>

            <div class="bg-slate-950/90 p-3 rounded-2xl border border-indigo-500/30 space-y-3 text-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="chk-pay-at-home" onchange="togglePayAtHome(this.checked)" class="w-4 h-4 rounded text-indigo-600 focus:ring-0 bg-slate-900 border-slate-700">
                        <label for="chk-pay-at-home" class="font-bold text-white cursor-pointer select-none">
                            ناردن بۆ دەرەوە (پارەدان لە کاتی گەیاندن / Pay at Home)
                        </label>
                    </div>
                    <span class="text-[10px] bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded-md font-mono">COD</span>
                </div>

                <div id="delivery-fields" class="space-y-2 pt-1">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" id="cust-name" placeholder="ناوی کڕیار (پێویستە)..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white outline-none focus:border-indigo-500 text-xs">
                        <input type="text" id="cust-phone" placeholder="مۆبایل (پێویستە)..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono outline-none focus:border-indigo-500 text-xs">
                    </div>
                    <input type="text" id="cust-address" placeholder="ناونیشانی تەواو: گەڕەک، کۆڵان، شوێنی گەیاندن..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white outline-none focus:border-indigo-500 text-xs">
                    <div id="delivery-error" class="hidden text-rose-400 text-[10px] font-bold"></div>
                </div>
            </div>

            <div class="space-y-2 pt-1">
                <button onclick="validateAndProcessCheckout(true)" id="btn-print-checkout" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 rounded-xl transition text-xs shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>چاپکردنی وەسڵ و تەواوکردن</span>
                </button>

                <button onclick="validateAndProcessCheckout(false)" id="btn-skip-print" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold py-2.5 rounded-xl transition text-xs border border-slate-700 flex items-center justify-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    <span>تەواوکردن بەبێ چاپ (Skip Print)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- مۆداڵی پارەدانی ئۆنلاین -->
    <div id="modal-digital-pay" class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-blue-500/40 max-w-md w-full rounded-3xl p-6 shadow-2xl space-y-4 text-right" dir="rtl">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-xl font-bold">💳</div>
                    <div>
                        <h3 class="font-black text-sm text-white">پارەدانی ئۆنلاین</h3>
                        <p class="text-[10px] text-slate-400">FIB & Card Gateway</p>
                    </div>
                </div>
                <button onclick="closeDigitalPayModal()" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 text-center space-y-1">
                <span class="text-[11px] text-slate-400 font-bold">بڕی پارەی داواکراو:</span>
                <div id="digital-pay-amount" class="font-mono text-3xl font-black text-emerald-400">0 {{ $settings['currency_symbol'] ?? 'د.ع' }}</div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">ژمارەی پسوولەی ئامێرەکە (Ref No / RRN):</label>
                    <input type="text" id="input-ref-no" placeholder="وەک: 894321" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white font-mono text-sm font-bold outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-slate-400 text-[11px] font-bold mb-1">جۆری پلاتفۆرم:</label>
                    <select id="select-card-bank" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-xs outline-none focus:border-blue-500">
                        <option value="FIB">FIB App / QR</option>
                        <option value="POS Card">ئامێری کارتی بانکی (POS)</option>
                        <option value="FastPay">فاستپەی (FastPay)</option>
                    </select>
                </div>
                <div id="digital-pay-error" class="hidden p-2 rounded-xl bg-rose-500/10 text-rose-400 font-bold text-[11px]"></div>
            </div>

            <div class="pt-2 flex gap-2">
                <button onclick="submitDigitalPayment()" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-black py-3 rounded-xl transition text-xs shadow-lg shadow-blue-600/20">پشتڕاستکردنەوە</button>
                <button type="button" onclick="closeDigitalPayModal()" class="bg-slate-800 text-slate-300 px-4 py-3 rounded-xl text-xs font-bold">پاشگەزبوونەوە</button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!--  چاپی گەرمی 80mm لەگەڵ لۆگۆ و دەقی خاوێنکراوە  -->
    <!-- ========================================== -->
    <div id="thermal-receipt-area" class="hidden text-black bg-white p-2 font-mono text-[11px] leading-tight">
        <div class="text-center pb-2 border-b border-dashed border-black">
            @if(!empty($settings['market_logo']))
                <div class="flex justify-center mb-1">
                    <img src="{{ $settings['market_logo'] }}" alt="Logo" class="max-h-12 max-w-[120px] object-contain filter grayscale contrast-200">
                </div>
            @endif
            <h2 class="font-bold text-base">{{ $settings['market_name'] ?? 'سوپەرمارکێت' }}</h2>
            @if(!empty($settings['market_slogan']))
                <div class="text-[9px] text-gray-700 font-normal">{{ $settings['market_slogan'] }}</div>
            @endif
            @if(!empty($settings['phone']) || !empty($settings['address']))
                <div class="text-[9px] text-gray-800 mt-0.5">{{ $settings['phone'] ?? '' }} | {{ $settings['address'] ?? '' }}</div>
            @endif
            <p id="receipt-type-banner" class="text-[11px] font-bold mt-1">وەسڵی فرۆشتن</p>
            <div class="flex justify-between items-center text-[9px] mt-1 pt-1 border-t border-dotted border-gray-400">
                <span id="receipt-invoice-no" class="font-bold"></span>
                <span id="receipt-date-time"></span>
            </div>
            <div class="flex justify-between items-center text-[9px] mt-0.5 text-gray-700">
                <span>کاشێر: {{ auth()->user()->name ?? 'کاشێر' }}</span>
                <span id="receipt-items-total-count"></span>
            </div>
        </div>

        <div id="receipt-delivery-section" class="hidden py-1.5 border-b border-dashed border-black text-right text-[10px] space-y-0.5">
            <div id="receipt-cod-alert" class="font-black text-center border border-black p-1 my-1 hidden">
                ⚠️ پارەدان لە کاتی گەیاندن (COD)
            </div>
            <div class="font-bold underline">زانیاریی کڕیار و گەیاندن:</div>
            <div id="receipt-cust-name"></div>
            <div id="receipt-cust-phone"></div>
            <div id="receipt-cust-address"></div>
        </div>

        <div class="py-2 border-b border-dashed border-black">
            <table class="w-full text-right text-[10px]">
                <thead>
                    <tr class="font-bold border-b border-black">
                        <th class="py-1">کاڵا</th>
                        <th class="py-1 text-center">دانە</th>
                        <th class="py-1 text-center">نرخی تاک</th>
                        <th class="py-1 text-left">کۆ</th>
                    </tr>
                </thead>
                <tbody id="receipt-items-tbody"></tbody>
            </table>
        </div>

        <div class="pt-2 text-right space-y-1 font-bold">
            <div class="flex justify-between text-sm border-b border-dotted border-black pb-1">
                <span>کۆی گشتی:</span>
                <span id="receipt-grand-total" class="font-black"></span>
            </div>
            <div class="flex justify-between text-[10px] pt-0.5">
                <span>شێوازی پارەدان:</span>
                <span id="receipt-payment-method"></span>
            </div>
            <div id="receipt-ref-container" class="hidden flex justify-between text-[10px]">
                <span>ژ.پسوولەی کارت (Ref):</span>
                <span id="receipt-ref-no" class="font-mono"></span>
            </div>
        </div>

        <!-- تەنها فوتەری داینامیکی بەبێ دەقی زیادە -->
        <div class="text-center pt-2 mt-2 border-t border-dashed border-black text-[9px] leading-normal font-bold">
            {{ $settings['receipt_footer'] ?? 'سوپاس بۆ سەردانەکەتان' }}
        </div>
    </div>

    <!-- مۆداڵی شیفت -->
    <div id="modal-start-shift" class="fixed inset-0 bg-slate-950/95 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-emerald-500/40 max-w-sm w-full rounded-3xl p-6 shadow-2xl space-y-4 text-right" dir="rtl">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl font-black">💵</div>
                <div><h3 class="font-extrabold text-sm text-white">دەستپێکردنی شیفت</h3><p class="text-[11px] text-slate-400">کاشی سەرەتایی سندوق</p></div>
            </div>
            <input type="number" id="input-open-cash" step="250" value="0" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white font-mono text-base font-black outline-none focus:border-emerald-500">
            <button onclick="submitOpenShift()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl transition text-xs">دەستپێکردن</button>
        </div>
    </div>

    <!-- مۆداڵی دەرچوونی کاتی -->
    <div id="modal-temp-logout" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 max-w-sm w-full rounded-3xl p-6 shadow-2xl space-y-4 text-right" dir="rtl">
            <h3 class="font-extrabold text-sm text-white">دەرچوونی کاتی</h3>
            <p class="text-xs text-slate-300">شیفتەکەت بە کراوەیی دەمێنێتەوە.</p>
            <div class="flex gap-2">
                <form action="{{ route('logout') }}" method="POST" class="flex-1">@csrf<button type="submit" class="w-full bg-slate-800 text-white py-2.5 rounded-xl text-xs">بەڵێ</button></form>
                <button type="button" onclick="closeTempLogoutModal()" class="flex-1 bg-slate-950 text-slate-400 py-2.5 rounded-xl text-xs">پاشگەزبوونەوە</button>
            </div>
        </div>
    </div>

    <!-- مۆداڵی داخستنی شیفت -->
    <div id="modal-close-shift" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 max-w-md w-full rounded-3xl p-6 shadow-2xl space-y-4 text-right" dir="rtl">
            <h3 class="font-extrabold text-sm text-white">داخستنی شیفت (Z-Report)</h3>
            <input type="number" id="input-actual-cash" step="250" placeholder="پارەی ژمێردراوی ناو سندوق..." class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white font-mono text-sm outline-none">
            <button onclick="submitCloseShift()" class="w-full bg-amber-600 text-white font-bold py-3 rounded-xl text-xs">داخستنی شیفت و دەرچوون</button>
        </div>
    </div>

    <script>
        const currencySymbol = "{{ $settings['currency_symbol'] ?? 'د.ع' }}";

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const styles = {
                success: 'bg-emerald-950/90 border-emerald-500/50 text-emerald-200',
                error: 'bg-rose-950/90 border-rose-500/50 text-rose-200',
                warning: 'bg-amber-950/90 border-amber-500/50 text-amber-200',
                info: 'bg-slate-900/95 border-slate-700 text-slate-200'
            };

            const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

            toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-2xl border shadow-2xl text-xs font-bold transition-all duration-300 transform translate-y-2 opacity-0 ${styles[type] || styles.info}`;
            toast.innerHTML = `<span>${icons[type] || 'ℹ️'}</span><span>${message}</span>`;

            container.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        let confirmResolve = null;
        function showConfirm({ title, message, subtitle = 'تکایە پشتڕاستی بکەرەوە', confirmText = 'بەڵێ', confirmColor = 'bg-emerald-600' }) {
            document.getElementById('confirm-title').innerText = title;
            document.getElementById('confirm-message').innerText = message;
            document.getElementById('confirm-subtitle').innerText = subtitle;

            const btnYes = document.getElementById('confirm-btn-yes');
            btnYes.innerText = confirmText;
            btnYes.className = `flex-1 text-white font-bold py-2.5 rounded-xl text-xs transition ${confirmColor}`;

            document.getElementById('modal-confirm').classList.remove('hidden');

            return new Promise((resolve) => {
                confirmResolve = resolve;
                btnYes.onclick = () => {
                    closeConfirmModal();
                    resolve(true);
                };
            });
        }

        function closeConfirmModal() {
            document.getElementById('modal-confirm').classList.add('hidden');
            if (confirmResolve) {
                confirmResolve(false);
                confirmResolve = null;
            }
        }

        let tabs = [{ id: 1, name: 'وەسڵی ١', items: [] }];
        let activeTabId = 1;
        let tabCounter = 1;
        let currentCheckoutMethod = 'pay_now';
        let lastConfirmedRefNo = null;

        function renderTabs() {
            const container = document.getElementById('tabs-container');
            if (!container) return;

            container.innerHTML = tabs.map(tab => {
                const isActive = tab.id === activeTabId;
                const totalItems = tab.items.reduce((sum, item) => sum + item.qty, 0);
                const activeClass = isActive ? 'bg-emerald-500 text-slate-950 font-black shadow-md' : 'bg-slate-900 text-slate-300 hover:bg-slate-800 font-medium';

                return `
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl cursor-pointer transition text-xs ${activeClass}" onclick="switchTab(${tab.id})">
                        <span>${tab.name}</span>
                        ${totalItems > 0 ? `<span class="px-1.5 py-0.2 rounded-full text-[10px] ${isActive ? 'bg-slate-950 text-emerald-400 font-bold' : 'bg-slate-800 text-slate-300'}">${totalItems}</span>` : ''}
                        ${tabs.length > 1 ? `<button onclick="closeTab(event, ${tab.id})" class="text-[11px] opacity-70 hover:opacity-100 hover:text-rose-400 ml-1">&times;</button>` : ''}
                    </div>
                `;
            }).join('');

            const activeTab = tabs.find(t => t.id === activeTabId);
            const labelEl = document.getElementById('current-tab-label');
            if (labelEl && activeTab) labelEl.innerText = activeTab.name;
            renderCart();
        }

        function addNewTab() {
            tabCounter++;
            tabs.push({ id: Date.now(), name: `وەسڵی ${tabCounter}`, items: [] });
            activeTabId = tabs[tabs.length - 1].id;
            renderTabs();
            document.getElementById('barcode-input').focus();
            showToast(`وەسڵی نوێ (${tabCounter}) کرایەوە`, 'info');
        }

        function switchTab(tabId) {
            activeTabId = tabId;
            renderTabs();
            document.getElementById('barcode-input').focus();
        }

        async function closeTab(event, tabId) {
            event.stopPropagation();
            if (tabs.length <= 1) return;
            const target = tabs.find(t => t.id === tabId);
            if (target && target.items.length > 0) {
                const confirmed = await showConfirm({
                    title: 'داخستنی وەسڵ',
                    message: `ئەم (${target.name})یە کاڵای تێدایە، دڵنیایت لە داخستنی؟`,
                    confirmColor: 'bg-rose-600 hover:bg-rose-500'
                });
                if (!confirmed) return;
            }
            tabs = tabs.filter(t => t.id !== tabId);
            if (activeTabId === tabId) activeTabId = tabs[0].id;
            renderTabs();
        }

        function getActiveTab() {
            return tabs.find(t => t.id === activeTabId) || tabs[0];
        }

        function renderCart() {
            const currentTab = getActiveTab();
            const container = document.getElementById('cart-items');
            if (!container) return;

            if (!currentTab || currentTab.items.length === 0) {
                container.innerHTML = `<div class="text-center text-slate-500 text-xs py-12">سەبەتە بەتاڵە</div>`;
                document.getElementById('cart-grand-total').innerText = '0 ' + currencySymbol;
                return;
            }

            let total = 0;
            container.innerHTML = currentTab.items.map((item, index) => {
                total += (item.price * item.qty);
                return `
                    <div class="py-2 flex items-center justify-between text-xs">
                        <div class="flex-1">
                            <div class="font-bold text-white">${item.name}</div>
                            <div class="text-[10px] text-slate-400 font-mono">${Number(item.price).toLocaleString()} ${currencySymbol}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="updateQty(${index}, -1)" class="w-6 h-6 rounded-lg bg-slate-800 text-white font-bold">-</button>
                            <span class="font-mono font-bold text-emerald-400 min-w-[20px] text-center">${item.qty}</span>
                            <button onclick="updateQty(${index}, 1)" class="w-6 h-6 rounded-lg bg-slate-800 text-white font-bold">+</button>
                            <button onclick="removeItem(${index})" class="text-rose-400 mr-2">&times;</button>
                        </div>
                    </div>
                `;
            }).join('');

            document.getElementById('cart-grand-total').innerText = Number(total).toLocaleString() + ' ' + currencySymbol;
        }

        function updateQty(index, delta) {
            const tab = getActiveTab();
            if (tab && tab.items[index]) {
                tab.items[index].qty += delta;
                if (tab.items[index].qty <= 0) tab.items.splice(index, 1);
                renderTabs();
            }
        }

        function removeItem(index) {
            const tab = getActiveTab();
            if (tab && tab.items[index]) { tab.items.splice(index, 1); renderTabs(); }
        }

        async function promptClearCart() {
            const tab = getActiveTab();
            if (!tab || tab.items.length === 0) return;
            const confirmed = await showConfirm({
                title: 'بەتاڵکردنەوەی سەبەتە',
                message: 'دڵنیایت لە بەتاڵکردنەوەی هەموو کاڵاکانی ناو ئەم سەبەتەیە؟',
                confirmColor: 'bg-rose-600 hover:bg-rose-500'
            });
            if (confirmed) {
                tab.items = [];
                renderTabs();
                showToast('سەبەتە بەتاڵکرایەوە', 'info');
            }
        }

        const mockProducts = [
            { id: 1, name: 'شیر نیدۆ ٩٠٠گم', price: 9500, barcode: '1001' },
            { id: 2, name: 'ڕۆنی زەیتی زەیتوون', price: 6500, barcode: '1002' },
            { id: 3, name: 'برنجی کوردی ١کگم', price: 2750, barcode: '1003' },
            { id: 4, name: 'شەکر ١کگم', price: 1250, barcode: '1004' },
            { id: 5, name: 'چای مەحموود ٥٠٠گم', price: 4500, barcode: '1005' }
        ];

        function renderProductsGrid() {
            const grid = document.getElementById('products-grid');
            if (!grid) return;
            grid.innerHTML = mockProducts.map(p => `
                <div onclick="addProduct(${p.id})" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 p-3 rounded-2xl cursor-pointer transition flex flex-col justify-between h-24">
                    <div class="font-bold text-xs text-white">${p.name}</div>
                    <div class="flex justify-between items-center pt-2"><span class="text-[10px] text-slate-400 font-mono">${p.barcode}</span><span class="font-mono font-bold text-emerald-400">${Number(p.price).toLocaleString()} ${currencySymbol}</span></div>
                </div>
            `).join('');
        }

        function addProduct(id) {
            const p = mockProducts.find(x => x.id === id);
            if (!p) return;
            const tab = getActiveTab();
            const ex = tab.items.find(x => x.id === p.id);
            if (ex) ex.qty++; else tab.items.push({ ...p, qty: 1 });
            renderTabs();
        }

        document.getElementById('barcode-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const code = this.value.trim();
                const p = mockProducts.find(x => x.barcode === code || x.name.includes(code));
                if (p) { addProduct(p.id); this.value = ''; }
            }
        });

        function handlePayAction(action) {
            const tab = getActiveTab();
            if (!tab || tab.items.length === 0) {
                showToast('تکایە سەرەتا کاڵا بخەرە ناو سەبەتە!', 'warning');
                return;
            }

            currentCheckoutMethod = action;
            lastConfirmedRefNo = null;
            const total = tab.items.reduce((sum, i) => sum + (i.price * i.qty), 0);

            if (action === 'pay_online') {
                document.getElementById('digital-pay-amount').innerText = Number(total).toLocaleString() + ' ' + currencySymbol;
                document.getElementById('input-ref-no').value = '';
                document.getElementById('digital-pay-error').classList.add('hidden');
                document.getElementById('modal-digital-pay').classList.remove('hidden');
                return;
            }

            openCheckoutFinalModal();
        }

        function closeDigitalPayModal() { document.getElementById('modal-digital-pay').classList.add('hidden'); }

        function submitDigitalPayment() {
            const refNo = document.getElementById('input-ref-no').value.trim();
            if (!refNo) {
                const err = document.getElementById('digital-pay-error');
                err.innerText = 'تکایە ژمارەی پسوولەی ئامێرەکە (Ref No) بنووسە.';
                err.classList.remove('hidden');
                return;
            }
            lastConfirmedRefNo = refNo;
            closeDigitalPayModal();
            openCheckoutFinalModal();
        }

        function togglePayAtHome(isChecked) {
            const btnSkip = document.getElementById('btn-skip-print');
            const name = document.getElementById('cust-name');

            if (isChecked) {
                btnSkip.classList.add('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
                btnSkip.setAttribute('title', 'بۆ داواکاریی دەرەوە دەبێت وەسڵ چاپ بکرێت');
                name.focus();
            } else {
                btnSkip.classList.remove('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
                btnSkip.removeAttribute('title');
            }
        }

        function openCheckoutFinalModal() {
            const tab = getActiveTab();
            const total = tab.items.reduce((sum, i) => sum + (i.price * i.qty), 0);
            
            const labels = {
                'pay_now': 'پارەدانی خێرا (کاش)',
                'pay_online': 'پارەدانی ئۆنلاین (Online)',
                'pay_later': 'پارەدانی دواکەوتوو (قەرز)'
            };

            document.getElementById('checkout-method-label').innerText = 'شێواز: ' + (labels[currentCheckoutMethod] || currentCheckoutMethod);
            document.getElementById('checkout-total-display').innerText = Number(total).toLocaleString() + ' ' + currencySymbol;
            
            document.getElementById('chk-pay-at-home').checked = false;
            document.getElementById('cust-name').value = '';
            document.getElementById('cust-phone').value = '';
            document.getElementById('cust-address').value = '';
            document.getElementById('delivery-error').classList.add('hidden');

            togglePayAtHome(false);
            document.getElementById('modal-checkout').classList.remove('hidden');
        }

        function closeCheckoutModal() { document.getElementById('modal-checkout').classList.add('hidden'); }

        function validateAndProcessCheckout(shouldPrint) {
            const isCOD = document.getElementById('chk-pay-at-home').checked;
            const cName = document.getElementById('cust-name').value.trim();
            const cPhone = document.getElementById('cust-phone').value.trim();
            const cAddress = document.getElementById('cust-address').value.trim();
            const errBox = document.getElementById('delivery-error');

            if (isCOD) {
                if (!shouldPrint) {
                    errBox.innerText = '⚠️ چاپکردنی وەسڵ ناچارییە بۆ داواکاریی دەرەوە و پارەدان لە ماڵەوە.';
                    errBox.classList.remove('hidden');
                    return;
                }

                if (!cName || !cPhone || !cAddress) {
                    errBox.innerText = '⚠️ بۆ پارەدان لە ماڵەوە، دەبێت ناو، مۆبایل، و ناونیشان بنووسرێت.';
                    errBox.classList.remove('hidden');
                    return;
                }
            }

            errBox.classList.add('hidden');
            processCheckout(shouldPrint, isCOD, cName, cPhone, cAddress);
        }

        function processCheckout(shouldPrint, isCOD, cName, cPhone, cAddress) {
            const tab = getActiveTab();
            if (!tab || tab.items.length === 0) return;

            const payload = {
                items: tab.items,
                payment_method: currentCheckoutMethod,
                reference_no: lastConfirmedRefNo,
                is_cod: isCOD,
                customer_name: cName,
                customer_phone: cPhone,
                customer_address: cAddress
            };

            fetch("{{ route('pos.checkout') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                const data = await res.json().catch(() => null);
                if (!res.ok || !data || !data.success) {
                    const msg = (data && data.message) ? data.message : `هەڵەی سێرڤەر (${res.status})`;
                    showToast(msg, 'error');
                    throw new Error(msg);
                }
                return data;
            })
            .then(data => {
                const invoiceNo = data.invoice_no;
                const total = data.grand_total;
                const now = new Date();

                showToast(`وەسڵی ${invoiceNo} بە سەرکەوتوویی تۆمارکرا`, 'success');

                if (shouldPrint) {
                    printReceiptPayload({
                        invoice_no: invoiceNo,
                        grand_total: total,
                        created_at: now.toLocaleString('en-GB'),
                        payment_method: currentCheckoutMethod,
                        is_cod: isCOD,
                        reference_no: lastConfirmedRefNo,
                        customer_name: cName,
                        customer_phone: cPhone,
                        customer_address: cAddress,
                        items: tab.items
                    });
                }

                closeCheckoutModal();
                if (tabs.length > 1) {
                    tabs = tabs.filter(t => t.id !== tab.id);
                    activeTabId = tabs[0].id;
                } else {
                    tab.items = [];
                }
                renderTabs();
            })
            .catch(err => console.error(err));
        }

        function printReceiptPayload(data) {
            document.getElementById('receipt-date-time').innerText = data.created_at || new Date().toLocaleString('en-GB');
            document.getElementById('receipt-invoice-no').innerText = 'ژ.پسوولە: ' + data.invoice_no;
            document.getElementById('receipt-grand-total').innerText = Number(data.grand_total).toLocaleString() + ' ' + currencySymbol;

            const totalItemCount = data.items.reduce((sum, i) => sum + (parseInt(i.qty || i.quantity) || 1), 0);
            document.getElementById('receipt-items-total-count').innerText = 'کۆی دانەکان: ' + totalItemCount;

            let methodTitle = 'پارەدانی خێرا (کاش)';
            if (data.is_cod || data.payment_method === 'cod') {
                methodTitle = 'پارەدان لە کاتی گەیاندن (COD)';
                document.getElementById('receipt-type-banner').innerText = '🚚 وەسڵی گەیاندن و ناردنی دەرەوە';
                document.getElementById('receipt-cod-alert').classList.remove('hidden');
            } else {
                document.getElementById('receipt-type-banner').innerText = 'وەسڵی فرۆشتن';
                document.getElementById('receipt-cod-alert').classList.add('hidden');
                if (data.payment_method === 'online' || data.payment_method === 'pay_online') methodTitle = 'ئۆنلاین / کارتی بانکی';
                if (data.payment_method === 'debt' || data.payment_method === 'pay_later') methodTitle = 'قەرز (Pay Later)';
            }
            document.getElementById('receipt-payment-method').innerText = methodTitle;

            const refCont = document.getElementById('receipt-ref-container');
            if (data.reference_no) {
                refCont.classList.remove('hidden');
                document.getElementById('receipt-ref-no').innerText = '#' + data.reference_no;
            } else {
                refCont.classList.add('hidden');
            }

            const delivSec = document.getElementById('receipt-delivery-section');
            if (data.customer_name || data.customer_phone || data.customer_address) {
                delivSec.classList.remove('hidden');
                document.getElementById('receipt-cust-name').innerText = data.customer_name ? 'کڕیار: ' + data.customer_name : '';
                document.getElementById('receipt-cust-phone').innerText = data.customer_phone ? 'مۆبایل: ' + data.customer_phone : '';
                document.getElementById('receipt-cust-address').innerText = data.customer_address ? 'ناونیشان: ' + data.customer_address : '';
            } else {
                delivSec.classList.add('hidden');
            }

            document.getElementById('receipt-items-tbody').innerHTML = data.items.map(i => {
                const name = i.name || i.product_name || 'کاڵا';
                const qty = parseInt(i.qty || i.quantity) || 1;
                const unitPrice = parseFloat(i.price || i.unit_price) || 0;
                const lineTotal = unitPrice * qty;

                return `
                    <tr class="border-b border-dotted border-gray-300">
                        <td class="py-1 font-bold">${name}</td>
                        <td class="py-1 text-center font-mono">${qty}</td>
                        <td class="py-1 text-center font-mono">${Number(unitPrice).toLocaleString()}</td>
                        <td class="py-1 text-left font-mono font-bold">${Number(lineTotal).toLocaleString()}</td>
                    </tr>
                `;
            }).join('');

            window.print();
        }

        let myInvoicesCache = [];

        function openMyInvoicesModal() {
            const listEl = document.getElementById('my-invoices-list');
            listEl.innerHTML = `<div class="text-center text-slate-500 py-8">چاوەڕوان بە...</div>`;
            document.getElementById('modal-my-invoices').classList.remove('hidden');

            fetch("{{ route('pos.my_invoices') }}")
                .then(res => res.json())
                .then(data => {
                    if (!data.success || !data.orders || data.orders.length === 0) {
                        listEl.innerHTML = `<div class="text-center text-slate-500 py-8">هیچ وەسڵێکت تۆمار نەکردووە.</div>`;
                        return;
                    }
                    myInvoicesCache = data.orders;
                    listEl.innerHTML = data.orders.map((ord, idx) => `
                        <div class="bg-slate-950 p-3.5 rounded-2xl border border-slate-800 flex items-center justify-between hover:border-slate-700 transition">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-white">${ord.invoice_no}</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-md font-bold ${ord.payment_method === 'cod' ? 'bg-indigo-500/20 text-indigo-400' : (ord.payment_method === 'online' ? 'bg-blue-500/20 text-blue-400' : 'bg-emerald-500/20 text-emerald-400')}">${ord.payment_method}</span>
                                </div>
                                <div class="text-[11px] text-slate-400">${ord.created_at} — (${ord.items_count} کاڵا)</div>
                                ${ord.customer_name ? `<div class="text-[10px] text-slate-400 font-bold">🚚 کڕیار: ${ord.customer_name} (${ord.customer_phone})</div>` : ''}
                            </div>
                            <div class="text-left space-y-1.5">
                                <div class="font-mono font-black text-sm text-emerald-400">${Number(ord.grand_total).toLocaleString()} ${currencySymbol}</div>
                                <button onclick="reprintMyInvoice(${idx})" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1 rounded-xl text-[11px] font-bold transition flex items-center gap-1 border border-slate-700">
                                    <span>چاپکردنەوە 🖨️</span>
                                </button>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(err => {
                    listEl.innerHTML = `<div class="text-center text-rose-400 py-8 font-bold">نەتوانرا وەسڵەکان بهێنرێت.</div>`;
                });
        }

        function closeMyInvoicesModal() { document.getElementById('modal-my-invoices').classList.add('hidden'); }

        function reprintMyInvoice(index) {
            const ord = myInvoicesCache[index];
            if (!ord) return;
            printReceiptPayload({
                invoice_no: ord.invoice_no,
                grand_total: ord.grand_total,
                created_at: ord.created_at,
                payment_method: ord.payment_method,
                is_cod: ord.payment_method === 'cod',
                reference_no: ord.reference_no,
                customer_name: ord.customer_name,
                customer_phone: ord.customer_phone,
                customer_address: ord.customer_address,
                items: ord.items
            });
        }

        function updateClock() {
            const timeEl = document.getElementById('system-time');
            if (timeEl) timeEl.innerText = new Date().toLocaleTimeString('en-GB', { timeZone: systemTimezone, hour12: false });
        }
        setInterval(updateClock, 1000);
        updateClock();

        function checkActiveShift() {
            fetch("{{ route('shift.current') }}")
                .then(res => res.json())
                .then(data => {
                    if (data.success && !data.has_open_shift) {
                        document.getElementById('modal-start-shift').classList.remove('hidden');
                    }
                });
        }
        checkActiveShift();

        function submitOpenShift() {
            const val = document.getElementById('input-open-cash').value;
            fetch("{{ route('shift.open') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
                body: JSON.stringify({ opening_cash: parseFloat(val) || 0 })
            }).then(res => res.json()).then(data => { 
                if (data.success) {
                    document.getElementById('modal-start-shift').classList.add('hidden');
                    showToast('شیفت بە سەرکەوتوویی دەستیپێکرد', 'success');
                }
            });
        }

        function openTempLogoutModal() { document.getElementById('modal-temp-logout').classList.remove('hidden'); }
        function closeTempLogoutModal() { document.getElementById('modal-temp-logout').classList.add('hidden'); }
        function closeShiftModal() { document.getElementById('modal-close-shift').classList.add('hidden'); }

        function openCloseShiftModal() {
            fetch("{{ route('shift.current') }}")
                .then(res => res.json())
                .then(data => {
                    if (!data.has_open_shift) { document.getElementById('modal-start-shift').classList.remove('hidden'); return; }
                    document.getElementById('modal-close-shift').classList.remove('hidden');
                });
        }

        function submitCloseShift() {
            const actual = document.getElementById('input-actual-cash').value;
            if (actual === '' || isNaN(actual)) {
                showToast('تکایە بڕی پارەی ژمێردراوی سندوق بنووسە', 'warning');
                return;
            }
            fetch("{{ route('shift.close') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
                body: JSON.stringify({ actual_cash: parseFloat(actual), notes: '' })
            }).then(res => res.json()).then(data => { 
                if (data.success) { 
                    showToast('شیفت داخرا، ڕاپۆرتی Z چاپ دەکرێت', 'success');
                    window.open(data.z_report_url, '_blank'); 
                    window.location.href = "{{ route('login') }}"; 
                }
            });
        }

        renderTabs();
        renderProductsGrid();
    </script>

@endsection