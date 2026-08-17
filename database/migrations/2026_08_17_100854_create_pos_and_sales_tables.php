<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ١. شیفتی کاشێر و چاودێری سندوق (Register Shifts)
        Schema::create('register_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('register_id')->constrained('registers')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('opening_cash', 14, 2)->default(0.00);
            $table->decimal('closing_cash', 14, 2)->nullable();
            $table->decimal('cash_difference', 14, 2)->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // ٢. ڕاگرتنی سەبەتە (Park / Suspended Orders)
        Schema::create('suspended_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('register_id')->constrained('registers')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('cart_data');
            $table->timestamp('parked_at')->useCurrent();
            $table->timestamps();
        });

        // ٣. کاتی ئۆفلاین (Offline POS Transactions)
        Schema::create('offline_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('register_id')->constrained('registers')->cascadeOnDelete();
            $table->json('payload');
            $table->string('sync_status')->default('pending'); // pending, synced, failed
            $table->timestamps();
        });

        // ٤. پارە داخڵکردن و دەرکردنی سندوق لە کاتی شیفت (Cash In / Cash Out / Drop)
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('register_shift_id')->constrained('register_shifts')->cascadeOnDelete();
            $table->string('type'); // cash_in, cash_out, safe_drop
            $table->decimal('amount', 14, 2);
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // ٥. پسوولەکانی فرۆشتن (Orders)
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique()->index();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUuid('register_shift_id')->constrained('register_shifts')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 2)->default(0.00);
            $table->decimal('grand_total', 14, 2)->default(0.00);
            $table->decimal('paid_amount', 14, 2)->default(0.00);
            $table->decimal('change_due', 14, 2)->default(0.00);
            $table->string('status')->default('completed'); // completed, suspended, refunded, voided
            $table->timestamps();
        });

        // ٦. کاڵاکانی ناو پسوولە (Order Items)
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('total_price', 14, 2);
            $table->timestamps();
        });

        // ٧. دابەشکردنی وەجبەی FIFO (Stock Allocations)
        Schema::create('stock_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignUuid('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_cost', 14, 2);
            $table->timestamps();
        });

        // ٨. پارەدانەکانی پسوولە (Payments)
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('method'); // cash, card, debt, loyalty_points
            $table->decimal('amount', 14, 2);
            $table->string('currency')->default('IQD');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->string('reference_number')->nullable();
            $table->timestamps();
        });

        // ٩. پسوولەی گەڕاندنەوە (Return Orders)
        Schema::create('return_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('refund_amount', 14, 2);
            $table->string('status')->default('completed'); // completed, cancelled
            $table->timestamps();
        });

        // ١٠. کاڵا گەڕاوەکان و دیاریکردنی دۆخ (Return Order Items)
        Schema::create('return_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('return_order_id')->constrained('return_orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('refund_price', 14, 2);
            $table->string('condition')->default('restock'); // restock (دەچێتەوە مەخزەن), damaged (دەچێتە تەلەف)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_order_items');
        Schema::dropIfExists('return_orders');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('stock_allocations');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('offline_transactions');
        Schema::dropIfExists('suspended_orders');
        Schema::dropIfExists('register_shifts');
    }
};