<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ١. پلانی ژمێریاری (Chart of Accounts)
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->index(); // 1010 Cash, 4010 Sales, 5010 COGS
            $table->string('name');
            $table->string('type'); // asset, liability, equity, revenue, expense
            $table->timestamps();
        });

        // ٢. بەڵگەنامە و سەرپەڕەی ژمێریاری (Journal Entries)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignUuid('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->string('source_type'); // pos_sale, purchase_receipt, expense, payroll, adjustment
            $table->timestamp('posted_at')->useCurrent();
            $table->timestamps();
        });

        // ٣. دێڕەکانی دێبیت و کرێدیت (Double-Entry Lines)
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignUuid('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('debit', 14, 2)->default(0.00);
            $table->decimal('credit', 14, 2)->default(0.00);
            $table->timestamps();
        });

        // ٤. خەرجییە گشتی و ڕۆژانەکان (General Expenses)
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUuid('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};