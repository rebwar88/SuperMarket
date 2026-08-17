<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ١. ڕێکخستنەکانی فرۆشگا (Store Settings)
        Schema::create('store_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->unique(['store_id', 'key']);
            $table->timestamps();
        });

        // ٢. نرخی ئاڵوگۆڕی دراو بەپێی بەروار (Currency Rates)
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('currency_code', 10); // USD, IQD
            $table->decimal('rate', 14, 4); // بۆ نموونە 1500.0000 بۆ 1 دۆلار
            $table->date('effective_date')->index();
            $table->timestamps();
        });

        // ٣. کورتەی فرۆشی ڕۆژانە بۆ ڕاپۆرتی خێرا (Daily Sales Rollup)
        Schema::create('daily_sales_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->date('report_date')->index();
            $table->decimal('total_sales', 14, 2)->default(0.00);
            $table->decimal('total_gross_profit', 14, 2)->default(0.00);
            $table->integer('total_transactions')->default(0);
            $table->timestamps();
        });

        // ٤. شیکاری قازانج و چالاکیی کاڵاکان بۆ ABC Analysis
        Schema::create('product_profitability_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->date('snapshot_date')->index();
            $table->decimal('margin_percent', 8, 2)->default(0.00);
            $table->decimal('units_sold', 12, 3)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_profitability_snapshots');
        Schema::dropIfExists('daily_sales_summaries');
        Schema::dropIfExists('currency_rates');
        Schema::dropIfExists('store_settings');
    }
};