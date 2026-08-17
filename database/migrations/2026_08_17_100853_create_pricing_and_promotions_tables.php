<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ١. ئۆفەر و پرۆمۆشنەکان (BOGO, Combo, Flat Discount)
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // bogo, fixed_discount, percentage, tiered
            $table->decimal('discount_value', 14, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        // ٢. یاساکانی نرخ و فرۆشتنی بەکۆمەڵ (Tiered / Quantity-based Pricing)
        Schema::create('price_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('scope')->default('retail'); // retail, wholesale, vip
            $table->decimal('min_qty', 10, 2)->default(1.00);
            $table->decimal('price', 14, 2);
            $table->timestamps();
        });

        // ٣. کۆپۆنی داشکاندن (Coupons)
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->index();
            $table->decimal('discount_value', 14, 2);
            $table->string('type')->default('fixed'); // fixed, percentage
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('price_rules');
        Schema::dropIfExists('promotions');
    }
};