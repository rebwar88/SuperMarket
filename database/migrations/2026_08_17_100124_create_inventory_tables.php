<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ١. بەش و پۆلێنەکان
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->timestamps();
        });

        // ٢. یەکەکانی پێوانە (دانە، کگم، لتر...)
        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('short_code')->unique(); // pcs, kg, ltr
            $table->boolean('allow_decimal')->default(false); // بۆ جیاکردنەوەی تەرازوو لە دانە
            $table->timestamps();
        });

        // ٣. کاڵاکان
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->decimal('cost_price', 14, 2)->default(0);
            $table->decimal('retail_price', 14, 2)->default(0);
            $table->integer('alert_quantity')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ٤. بارکۆدە فرەجۆرەکان (Unit, Pack, Carton, Scale)
        Schema::create('barcodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('code')->unique()->index();
            $table->string('type')->default('unit'); // unit, pack, carton, scale
            $table->decimal('packing_qty', 10, 2)->default(1.00); // چەند دانەی تێدایە
            $table->timestamps();
        });

        // ٥. وەجبەکان و بەسەرچوون (FIFO Batches)
        Schema::create('batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('batch_number');
            $table->decimal('stock_qty', 12, 3)->default(0);
            $table->decimal('purchase_cost', 14, 2)->default(0);
            $table->date('expiry_date')->nullable()->index();
            $table->timestamps();
        });

        // ٦. حجزکردنی کاتیی مەخزەن لە کاتی چێکئاوت (Concurrency / Overselling Prevention)
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->uuid('order_id')->nullable()->index();
            $table->decimal('quantity', 12, 3);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // ٧. تەواوی جوڵەکانی مەخزەن (Audit Movement)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('type'); // purchase, sale, transfer_in, transfer_out, adjustment, return, wastage
            $table->decimal('quantity', 12, 3);
            $table->timestamps();
        });

        // ٨. سەرژمێری کۆگا (Stock Counts)
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('status')->default('in_progress'); // in_progress, completed, cancelled
            $table->timestamp('counted_at')->nullable();
            $table->timestamps();
        });

        // ٩. کاڵاکانی سەرژمێری و بەراوردکاری
        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('counted_qty', 12, 3);
            $table->decimal('system_qty', 12, 3);
            $table->timestamps();
        });

        // ١٠. تەلەف و خراپبوون (Wastages)
        Schema::create('wastages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->string('reason'); // expired, damaged, spillage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wastages');
        Schema::dropIfExists('stock_count_items');
        Schema::dropIfExists('stock_counts');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('barcodes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
    }
};