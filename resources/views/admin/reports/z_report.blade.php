@extends('layouts.admin')

@section('title', 'ڕاپۆرتی کۆتایی ڕۆژ (Z-Report)')

@section('content')
    <!-- سەردێڕ و دوگمەی چاپکردن -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-extrabold text-base text-white">ڕاپۆرتی کۆتایی ڕۆژ و شیفت (Z-Report)</h2>
            <p class="text-xs text-slate-400">کۆی داهات، فرۆشراوەکان، باڵانسی کاشێر و سندوقی شیفت</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- فۆڕمی گۆڕینی شیفت -->
            <form method="GET" action="{{ url('/reports/z-report') }}" class="flex items-center gap-2">
                <select name="shift_select" onchange="if(this.value) window.location.href='/reports/z-report/' + this.value" class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 outline-none focus:border-indigo-500">
                    <option value="">-- هەڵبژاردنی شیفت --</option>
                    @foreach($allShifts as $s)
                        <option value="{{ $s->id }}" {{ ($shift && $shift->id == $s->id) ? 'selected' : '' }}>
                            شیفتی #{{ substr($s->id, 0, 8) }} | {{ $s->cashier_name ?? 'بێ ناو' }} ({{ date('Y-m-d H:i', strtotime($s->opened_at)) }})
                        </option>
                    @endforeach
                </select>
            </form>

            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-lg shadow-indigo-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>چاپکردنی ڕاپۆرت</span>
            </button>
        </div>
    </div>

    @if(!$shift)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center text-slate-400 text-xs">
            هیچ شیفتێک لە داتابەیسدا نەدۆزرایەوە.
        </div>
    @else
        <!-- پەڕەی ڕاپۆرت بە ستایلی پسوولەی پرۆفیشناڵ -->
        <div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-xs" id="printable-z-report">
            
            <!-- سەردێڕی ڕاپۆرت -->
            <div class="text-center border-b border-slate-800 pb-5 space-y-1">
                <h1 class="text-lg font-black text-white">{{ $settings['market_name'] }}</h1>
                <p class="text-slate-400 text-[11px]">{{ $settings['market_address'] }} | {{ $settings['market_phone'] }}</p>
                <div class="inline-block bg-indigo-950/80 text-indigo-300 font-extrabold px-3 py-1 rounded-lg text-xs mt-2 border border-indigo-500/30">
                    ڕاپۆرتی داخستنی شیفت (Z-REPORT)
                </div>
            </div>

            <!-- زانیاری شیفت و کات -->
            <div class="grid grid-cols-2 gap-3 bg-slate-950/60 p-4 rounded-2xl border border-slate-800/80 text-slate-300">
                <div>
                    <span class="text-slate-500 block text-[10px]">کاشێر:</span>
                    <strong class="text-white">{{ $cashier->name ?? 'کاشێر' }} ({{ $cashier->username ?? '' }})</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[10px]">سندوق / ئامێر:</span>
                    <strong class="text-white">{{ $register->name ?? 'سندوقی سەرەکی' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[10px]">کاتی کرانەوە:</span>
                    <span class="font-mono text-slate-300">{{ date('Y-m-d H:i:s', strtotime($shift->opened_at)) }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block text-[10px]">کاتی داخران:</span>
                    <span class="font-mono text-slate-300">
                        {{ $shift->closed_at ? date('Y-m-d H:i:s', strtotime($shift->closed_at)) : 'هێشتا کراوەیە (Open)' }}
                    </span>
                </div>
            </div>

            <!-- کورتەی ژمارەکان و فرۆشتن -->
            <div class="space-y-2 border-b border-slate-800 pb-4">
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">ژمارەی پسوولەکانی فرۆشتن:</span>
                    <strong class="font-mono text-white">{{ number_format($summary['total_orders']) }}</strong>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">کۆی ژمارەی کاڵا فرۆشراوەکان:</span>
                    <strong class="font-mono text-white">{{ number_format($summary['total_items_sold']) }} دانە</strong>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">کۆی گشتی پێش داشکاندن:</span>
                    <span class="font-mono text-slate-300">{{ number_format($summary['subtotal'], 0) }} {{ $settings['currency_symbol'] }}</span>
                </div>
                <div class="flex justify-between py-1 text-rose-400">
                    <span>کۆی داشکاندنەکان:</span>
                    <span class="font-mono">- {{ number_format($summary['discount_total'], 0) }} {{ $settings['currency_symbol'] }}</span>
                </div>
                <div class="flex justify-between py-2 border-t border-slate-800/80 font-black text-sm text-emerald-400">
                    <span>کۆی پەتی فرۆشتن (Net Sales):</span>
                    <span class="font-mono">{{ number_format($summary['grand_total'], 0) }} {{ $settings['currency_symbol'] }}</span>
                </div>
            </div>

            <!-- باڵانسی سندوق و پارەی سەرەتایی/کۆتایی -->
            <div class="space-y-2 bg-slate-950/40 p-4 rounded-2xl border border-slate-800/60">
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">پارەی سەرەتای شیفت (Opening Cash):</span>
                    <span class="font-mono text-slate-300">{{ number_format((float)($shift->opening_cash ?? 0), 0) }} {{ $settings['currency_symbol'] }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">پارەی کۆتایی شیفت (Closing Cash):</span>
                    <span class="font-mono text-slate-300">{{ number_format((float)($shift->closing_cash ?? 0), 0) }} {{ $settings['currency_symbol'] }}</span>
                </div>
                @if(!empty($shift->notes))
                    <div class="pt-2 border-t border-slate-800 text-[11px] text-slate-400">
                        <strong>تێبینی:</strong> {{ $shift->notes }}
                    </div>
                @endif
            </div>

            <!-- بەشی واژوو بۆ چاپ -->
            <div class="pt-6 grid grid-cols-2 text-center text-slate-400 text-xs border-t border-slate-800">
                <div>
                    <p class="mb-8 text-slate-500">واژووی کاشێر</p>
                    <p class="border-t border-dashed border-slate-700 mx-8 pt-1 text-[11px] text-slate-400">{{ $cashier->name ?? 'کاشێر' }}</p>
                </div>
                <div>
                    <p class="mb-8 text-slate-500">واژووی بەڕێوەبەر</p>
                    <p class="border-t border-dashed border-slate-700 mx-8 pt-1 text-[11px] text-slate-400">بەڕێوەبەری سندوق</p>
                </div>
            </div>

        </div>
    @endif
@endsection
