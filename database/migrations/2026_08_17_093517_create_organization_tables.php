<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ١. کۆمپانیا / براند
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('tax_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // ٢. لق و فرۆشگاکان
        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('receipt_header')->nullable();
            $table->string('receipt_footer')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        // ٣. مەخزەن و کۆگاکان
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('retail'); // retail, back_store, central
            $table->text('location')->nullable();
            $table->timestamps();
        });

        // ٤. ئامێرەکانی کاشێر (POS Registers)
        Schema::create('registers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name'); // Lane 1, Cashier 1
            $table->string('code')->unique();
            $table->string('status')->default('active'); // active, maintenance, closed
            $table->timestamps();
        });

        // ٥. بەستنەوەی کارمەندان بە لق و ڕۆڵەکان (Pivot)
        Schema::create('store_users', function (Blueprint $table) {
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('cashier'); // manager, supervisor, cashier, stock_clerk
            $table->primary(['store_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_users');
        Schema::dropIfExists('registers');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('companies');
    }
};