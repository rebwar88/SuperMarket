@extends('layouts.admin')

@section('title', 'ڕێکخستنەکان')

@section('content')
    @if(session('success'))
        <div class="bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 p-4 rounded-2xl text-xs font-bold flex items-center gap-2">
            <span class="text-base">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto space-y-6">
        @csrf

        <!-- ١. لۆگۆ و زانیارییەکانی فرۆشگا -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
            <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                <div class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-sm font-bold">🏪</div>
                <h2 class="font-bold text-sm text-white">لۆگۆ و زانیارییەکانی مارکێت</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <div class="flex flex-col items-center justify-center p-4 bg-slate-950 border border-slate-800 rounded-2xl space-y-2">
                    <span class="text-xs font-bold text-slate-400">لۆگۆی ئێستا:</span>
                    @if(!empty($settings['market_logo']))
                        <img src="{{ $settings['market_logo'] }}" alt="Logo" class="w-20 h-20 object-contain rounded-xl border border-slate-700 p-1 bg-white">
                    @else
                        <div class="w-20 h-20 rounded-xl bg-slate-900 border border-dashed border-slate-700 flex items-center justify-center text-slate-500 text-xs">بێ لۆگۆ</div>
                    @endif
                    <label class="cursor-pointer bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold px-3 py-1.5 rounded-xl border border-slate-700 transition">
                        <span>گۆڕینی لۆگۆ</span>
                        <input type="file" name="market_logo_file" accept="image/*" class="hidden">
                    </label>
                </div>

                <div class="md:col-span-2 space-y-3 text-xs">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">ناوی سوپەرمارکێت:</label>
                        <input type="text" name="market_name" value="{{ $settings['market_name'] ?? '' }}" required class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-blue-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">دروشم / تایتڵی لاوەکی (Slogan):</label>
                        <input type="text" name="market_slogan" value="{{ $settings['market_slogan'] ?? '' }}" placeholder="وەک: باشترین کواڵیتی و گونجاوترین نرخ" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs pt-2">
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5">ژمارەی تەلەفۆن:</label>
                    <input type="text" name="phone" value="{{ $settings['phone'] ?? '' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5">ناونیشانی مارکێت:</label>
                    <input type="text" name="address" value="{{ $settings['address'] ?? '' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- ٢. ناوچەی کاتی و دراو و کۆگا -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
            <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm font-bold">⏰</div>
                <h2 class="font-bold text-sm text-white">ناوچەی کاتی و دراو و کۆگا</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5">ناوچەی کاتی (Timezone):</label>
                    <select name="timezone" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-white outline-none focus:border-emerald-500 font-mono">
                        <option value="Asia/Baghdad" {{ ($settings['timezone'] ?? 'Asia/Baghdad') === 'Asia/Baghdad' ? 'selected' : '' }}>کوردستان / عێراق (+03:00)</option>
                        <option value="Asia/Riyadh" {{ ($settings['timezone'] ?? '') === 'Asia/Riyadh' ? 'selected' : '' }}>ڕیاز (+03:00)</option>
                        <option value="Asia/Dubai" {{ ($settings['timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' }}>دوبەی (+04:00)</option>
                        <option value="Asia/Istanbul" {{ ($settings['timezone'] ?? '') === 'Asia/Istanbul' ? 'selected' : '' }}>ئیستەنبوڵ (+03:00)</option>
                        <option value="UTC" {{ ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5">هێمای دراو:</label>
                    <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? 'د.ع' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-emerald-500 font-bold">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5">نرخی ١٠٠ دۆلار:</label>
                    <input type="number" name="usd_exchange_rate" value="{{ $settings['usd_exchange_rate'] ?? '150000' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white font-mono outline-none focus:border-emerald-500">
                </div>
            </div>
        </div>

        <!-- ٣. کۆنتڕۆڵی سندوق -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
            <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-sm font-bold">💳</div>
                <h2 class="font-bold text-sm text-white">کۆنتڕۆڵی سندوق (POS Rules)</h2>
            </div>

            <div class="space-y-3 text-xs">
                <label class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800 rounded-2xl cursor-pointer hover:border-slate-700 transition">
                    <div>
                        <div class="font-bold text-white">ڕێگەدان بە فرۆشتنی قەرز (Pay Later)</div>
                        <div class="text-[11px] text-slate-400">شاردنەوە یان قفڵکردنی دوگمەی قەرز لەسەر سندوق.</div>
                    </div>
                    <input type="checkbox" name="allow_pay_later" value="1" {{ ($settings['allow_pay_later'] ?? '1') === '1' ? 'checked' : '' }} class="w-5 h-5 rounded text-emerald-500 bg-slate-900 border-slate-700 focus:ring-0">
                </label>

                <label class="flex items-center justify-between p-3.5 bg-slate-950 border border-slate-800 rounded-2xl cursor-pointer hover:border-slate-700 transition">
                    <div>
                        <div class="font-bold text-white">ڕێگەدان بە پارەدانی ئۆنلاین (Card Gateway)</div>
                        <div class="text-[11px] text-slate-400">چالاککردنی دوگمە و دەروازەی کارت لە سندوق.</div>
                    </div>
                    <input type="checkbox" name="allow_online_pay" value="1" {{ ($settings['allow_online_pay'] ?? '1') === '1' ? 'checked' : '' }} class="w-5 h-5 rounded text-blue-500 bg-slate-900 border-slate-700 focus:ring-0">
                </label>
            </div>
        </div>

        <!-- ٤. دەق و فوتەری وەسڵ -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
            <div class="flex items-center gap-2.5 border-b border-slate-800 pb-3">
                <div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-sm font-bold">🖨️</div>
                <h2 class="font-bold text-sm text-white">دەق و فوتەری وەسڵی چاپکراو</h2>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5">دەقی هێدەری سەر وەسڵ:</label>
                    <input type="text" name="receipt_header" value="{{ $settings['receipt_header'] ?? '' }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1.5">دەقی خوارەوەی وەسڵ (Receipt Footer):</label>
                    <textarea name="receipt_footer" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-3 text-white outline-none focus:border-purple-500">{{ $settings['receipt_footer'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black px-8 py-3.5 rounded-2xl text-xs transition shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>پاشەکەوتکردنی هەموو ڕێکخستنەکان</span>
            </button>
        </div>

    </form>
@endsection
