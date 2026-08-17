<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ١. کۆمپانیاکانی دابینکار (Suppliers)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('total_balance', 14, 2)->default(0.00); // قەرزی کۆمپانیا
            $table->timestamps();
        });

        // ٢. داواکاری و فاکتوورەی کڕین (Purchase Orders)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('po_number')->unique()->index();
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->string('status')->default('draft'); // draft, ordered, received, cancelled
            $table->timestamps();
        });

        // ٣. کاڵاکانی ناو فاکتوورەی کڕین
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('cost_price', 14, 2);
            $table->timestamps();
        });

        // ٤. وەرگرتنی مەخزەن (Goods Received Notes - GRN)
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('grn_number')->unique();
            $table->timestamp('received_at');
            $table->timestamps();
        });

        // ٥. پارەدان بە دابینکاران (Supplier Payments)
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->default('cash'); // cash, bank_transfer, cheque
            $table->timestamps();
        });

        // ٦. دەفتەری حسابی دابینکار (Supplier Ledger)
        Schema::create('supplier_ledger', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('entry_type'); // invoice, payment, return, adjustment
            $table->decimal('amount', 14, 2);
            $table->decimal('running_balance', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_ledger');
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('goods_received_notes');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};