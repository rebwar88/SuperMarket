<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ١. گروپەکانی کڕیاران (VIP، ئاسایی، فرۆشتنی بەکۆمەڵ)
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->timestamps();
        });

        // ٢. کڕیاران و حسابی قەرز
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable()->index();
            $table->decimal('total_debt', 14, 2)->default(0.00);
            $table->decimal('debt_limit', 14, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ٣. ناونیشانی کڕیاران
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('label')->default('home'); // home, work
            $table->text('address_line');
            $table->timestamps();
        });

        // ٤. حسابی خاڵەکانی پاداشت (Loyalty Account)
        Schema::create('loyalty_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->unique()->constrained('customers')->cascadeOnDelete();
            $table->integer('points_balance')->default(0);
            $table->timestamps();
        });

        // ٥. مێژووی وەرگرتن و خەرجکردنی خاڵەکان
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('loyalty_account_id')->constrained('loyalty_accounts')->cascadeOnDelete();
            $table->integer('points'); // دەکرێت موجەب بێت (دەستکەوت) یان سالب (خەرجکراو)
            $table->string('type'); // earned, redeemed, adjusted, expired
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_accounts');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_groups');
    }
};