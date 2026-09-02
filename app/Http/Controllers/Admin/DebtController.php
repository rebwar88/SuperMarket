<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\CRM\Models\Party;
use App\Domains\CRM\Models\PartyPayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DebtController extends Controller
{
    public function index(): View
    {
        $this->ensureTablesExist();

        // هێنانی ڕێکخستنەکان
        $settingsRaw = DB::table('store_settings')->pluck('value', 'key')->toArray();
        $defaults = [
            'market_name' => 'سوپەرمارکێت',
            'currency_symbol' => 'د.ع',
            'allow_pay_later' => '1',
        ];
        $settings = array_merge($defaults, $settingsRaw);

        $customers = Party::whereIn('type', ['customer', 'both'])->latest()->get();
        $suppliers = Party::whereIn('type', ['supplier', 'both'])->latest()->get();

        $totalCustomerDebt = (float) Party::whereIn('type', ['customer', 'both'])->sum('current_balance');
        $totalSupplierDebt = (float) Party::whereIn('type', ['supplier', 'both'])->sum('current_balance');

        $recentPayments = PartyPayment::with('party')->latest()->take(10)->get();

        return view('admin.debts.index', compact(
            'customers',
            'suppliers',
            'totalCustomerDebt',
            'totalSupplierDebt',
            'recentPayments',
            'settings'
        ));
    }

    public function storeParty(Request $request): RedirectResponse
    {
        $this->ensureTablesExist();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:customer,supplier,both'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'opening_balance' => ['nullable', 'numeric'],
        ]);

        Party::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'type' => $validated['type'],
            'credit_limit' => (float) ($validated['credit_limit'] ?? 0),
            'current_balance' => (float) ($validated['opening_balance'] ?? 0),
        ]);

        return redirect()->route('admin.debts.index')->with('success', 'کڕیار / دابینکەر بە سەرکەوتوویی تۆمارکرا.');
    }

    public function recordPayment(Request $request): RedirectResponse
    {
        $this->ensureTablesExist();

        $validated = $request->validate([
            'party_id' => ['required', 'string', 'exists:parties,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_type' => ['required', 'in:receipt,payout'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $party = Party::findOrFail($validated['party_id']);
        $amount = (float) $validated['amount'];

        PartyPayment::create([
            'party_id' => $party->id,
            'amount' => $amount,
            'payment_type' => $validated['payment_type'],
            'payment_method' => 'cash',
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($validated['payment_type'] === 'receipt') {
            $party->decrement('current_balance', $amount);
        } else {
            $party->decrement('current_balance', $amount);
        }

        return redirect()->route('admin.debts.index')->with('success', 'تۆماری وەرگرتن/پێدانی پارە بە سەرکەوتوویی جێبەجێکرا.');
    }

    private function ensureTablesExist(): void
    {
        if (!Schema::hasTable('parties')) {
            Schema::create('parties', function ($table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('type')->default('customer');
                $table->decimal('credit_limit', 15, 2)->default(0);
                $table->decimal('current_balance', 15, 2)->default(0);
                $table->uuid('account_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('party_payments')) {
            Schema::create('party_payments', function ($table) {
                $table->uuid('id')->primary();
                $table->uuid('party_id');
                $table->decimal('amount', 15, 2);
                $table->string('payment_type')->default('receipt');
                $table->string('payment_method')->default('cash');
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }
    }
}
